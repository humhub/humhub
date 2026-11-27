<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\file\libs\FileMetadata;
use humhub\modules\file\libs\FileVariants;
use humhub\modules\file\libs\Metadata;
use humhub\modules\file\models\File;
use humhub\modules\file\models\FileHistory;
use humhub\modules\file\serializers\FileSerializer;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class FileVariantsTest extends HumHubDbTestCase
{
    private const VTT = "WEBVTT\n\n00:00:01.000 --> 00:00:03.500\nHello\n";

    public function testDefaultPersistentVariants()
    {
        $this->assertSame(['old-*', 'subtitles-*', 'captions-*'], Yii::$app->getModule('file')->persistentVariants);
    }

    public function testWritableNames()
    {
        foreach (['subtitles-de.vtt', 'captions-de.vtt', 'subtitles-pt-BR.vtt', 'captions-zh-Hant.vtt'] as $name) {
            $this->assertTrue(FileVariants::isWritable($name), $name);
            $this->assertSame('text/vtt; charset=utf-8', FileVariants::getContentType($name), $name);
        }

        foreach (['subtitles-d.vtt', 'subtitles-DE.vtt', 'subtitles-de.txt', 'subtitles-de.vtt.php', 'subtitles-.vtt', 'old-1', 'preview-image', 'file'] as $name) {
            $this->assertFalse(FileVariants::isWritable($name), $name);
            $this->assertNull(FileVariants::getContentType($name), $name);
        }
    }

    public function testPutStoresTheVariant()
    {
        $file = $this->prepareFile('video/mp4');
        $variants = new FileVariants($file);

        $this->assertSame([], $variants->put('captions-de.vtt', self::VTT));

        $this->assertSame(self::VTT, $file->store->getContent('captions-de.vtt'));
        $this->assertTrue($variants->has('captions-de.vtt'));
        $this->assertNull(File::findOne(['id' => $file->id])->getAttribute('metadata'));
    }

    public function testPutConvertsSrt()
    {
        $file = $this->prepareFile('audio/mpeg');

        $errors = (new FileVariants($file))->put('subtitles-en.vtt', "1\r\n00:00:01,000 --> 00:00:02,000\r\nHi\r\n");

        $this->assertSame([], $errors);
        $this->assertSame("WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nHi\n", $file->store->getContent('subtitles-en.vtt'));
    }

    public function testPutRefusesInvalidInput()
    {
        $media = $this->prepareFile('video/mp4');
        $variants = new FileVariants($media);

        $this->assertArrayHasKey('name', $variants->put('subtitles-xx!.vtt', self::VTT));
        $this->assertArrayHasKey('name', $variants->put('other.vtt', self::VTT));
        $this->assertArrayHasKey('name', $variants->put('preview-image', self::VTT));
        $this->assertArrayHasKey('name', $variants->put('old-1', self::VTT));

        $errors = $variants->put('subtitles-de.vtt', "WEBVTT\n\n00:00:01.000 --> 00:00:03,500\nHi");
        $this->assertStringStartsWith('Line 3:', $errors['content'][0]);

        $this->assertArrayHasKey('content', $variants->put('subtitles-de.vtt', "WEBVTT\n\n" . str_repeat('x', 1048576)));
        $this->assertArrayHasKey('content', $variants->put('subtitles-de.vtt', "WEBVTT\n\n00:00:01.000 --> 00:00:03.500\n\xFF"));

        $this->assertSame([], $variants->getAll());

        $text = $this->prepareFile('text/plain');
        $this->assertArrayHasKey('name', (new FileVariants($text))->put('subtitles-de.vtt', self::VTT));
        $this->assertFalse($text->store->has('subtitles-de.vtt'));
    }

    public function testDeleteRemovesOnlyThatVariant()
    {
        $file = $this->prepareFile('video/mp4');
        $variants = new FileVariants($file);
        $variants->put('subtitles-de.vtt', self::VTT);
        $variants->put('subtitles-en.vtt', self::VTT);
        $file->store->setContent('x', 'preview-image');

        $this->assertTrue($variants->delete('subtitles-de.vtt'));

        $this->assertFalse($file->store->has('subtitles-de.vtt'));
        $this->assertTrue($file->store->has('subtitles-en.vtt'));
        $this->assertTrue($file->store->has());
        $this->assertFalse($variants->delete('subtitles-de.vtt'));
        // Only the writable names
        $this->assertFalse($variants->delete('preview-image'));
        $this->assertTrue($file->store->has('preview-image'));
    }

    public function testVariantsSurviveNewContent()
    {
        $file = $this->prepareFile('video/mp4');
        $file->setStoredFileContent('V1');
        (new FileVariants($file))->put('subtitles-de.vtt', self::VTT);
        $file->store->setContent('x', 'preview-image');

        $file->setStoredFileContent('V2');

        $this->assertSame('V2', $file->store->getContent());
        $this->assertSame(self::VTT, $file->store->getContent('subtitles-de.vtt'));
        $this->assertFalse($file->store->has('preview-image'));
    }

    public function testVariantsSurviveDeleteVariantsCommand()
    {
        $file = $this->prepareFile('video/mp4');
        $file->setStoredFileContent('V1');
        (new FileVariants($file))->put('subtitles-de.vtt', self::VTT);
        $file->store->setContent('x', 'preview-image');
        $file->store->setContent('y', FileHistory::VARIANT_PREFIX . '1');

        $command = new \humhub\modules\file\commands\FileController('file', Yii::$app);
        ob_start();
        $command->actionDeleteVariants();
        ob_end_clean();

        $this->assertTrue($file->store->has());
        $this->assertTrue($file->store->has('subtitles-de.vtt'));
        $this->assertTrue($file->store->has(FileHistory::VARIANT_PREFIX . '1'));
        $this->assertFalse($file->store->has('preview-image'));
    }

    public function testAModuleCanAddPersistentVariants()
    {
        $module = Yii::$app->getModule('file');
        $file = $this->prepareFile('video/mp4');
        $file->setStoredFileContent('V1');
        $file->store->setContent('x', 'custom-a');

        $module->persistentVariants[] = 'custom-*';
        try {
            $file->setStoredFileContent('V2');
            $this->assertTrue($file->store->has('custom-a'));
        } finally {
            array_pop($module->persistentVariants);
        }
    }

    public function testPatternsMatchByPrefixOrExactName()
    {
        $file = $this->prepareFile('video/mp4');
        $file->setStoredFileContent('V1');
        foreach (['ai-1', 'mai-x', 'preview-image'] as $name) {
            $file->store->setContent('x', $name);
        }

        $this->assertEqualsCanonicalizing(['mai-x', 'preview-image'], $file->store->getVariants(['ai-*']));
        $this->assertEqualsCanonicalizing(['ai-1', 'mai-x'], $file->store->getVariants(['preview-image']));
        $this->assertEqualsCanonicalizing(['ai-1', 'mai-x', 'preview-image'], $file->store->getVariants(['preview']));

        $file->store->delete(null, ['file', 'ai-*']);
        $this->assertTrue($file->store->has('ai-1'));
        $this->assertTrue($file->store->has());
        $this->assertFalse($file->store->has('mai-x'));
        $this->assertFalse($file->store->has('preview-image'));
    }

    public function testFailedWriteThrows()
    {
        $module = Yii::$app->getModule('file');
        $class = $module->storageManagerClass;
        $file = $this->prepareFile('video/mp4');

        $module->storageManagerClass = SilentStorageManager::class;
        try {
            $file = File::findOne(['id' => $file->id]);
            (new FileVariants($file))->put('subtitles-de.vtt', self::VTT);
            $this->fail('An exception is expected');
        } catch (\yii\base\Exception) {
        } finally {
            $module->storageManagerClass = $class;
        }
    }

    public function testAStaleRecordKeepsWhatAnotherWriterStored()
    {
        $file = $this->prepareFile('video/mp4');
        $stale = File::findOne(['id' => $file->id]);

        FileMetadata::update($file, static function (Metadata $metadata): void {
            $metadata->duration = 12.5;
        });
        FileMetadata::update($stale, static function (Metadata $metadata): void {
            $metadata->width = 640;
        });

        $metadata = File::findOne(['id' => $file->id])->getMetadata();
        $this->assertSame(12.5, $metadata->duration);
        $this->assertSame(640, $metadata->width);
    }

    public function testListsAllVariantsOfTheStorage()
    {
        $file = $this->prepareFile('video/mp4');
        $variants = new FileVariants($file);
        $variants->put('subtitles-pt-BR.vtt', self::VTT);
        $variants->put('captions-de.vtt', self::VTT);
        $file->store->setContent('preview', 'preview-image');
        $file->store->setContent('old content', FileHistory::VARIANT_PREFIX . '7');

        $list = FileSerializer::variants($file);

        // By name, without the content itself
        $this->assertSame(['captions-de.vtt', 'old-7', 'preview-image', 'subtitles-pt-BR.vtt'], array_column($list, 'name'));
        $this->assertSame([true, true, false, true], array_column($list, 'persistent'));

        $this->assertSame(['name', 'persistent', 'mimeType', 'size', 'updatedAt', 'url'], array_keys($list[0]));
        $this->assertSame('text/vtt', $list[0]['mimeType']);
        $this->assertSame(strlen(self::VTT), $list[0]['size']);
        $this->assertSame(strlen('preview'), $list[2]['size']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d\+00:00$/', $list[0]['updatedAt']);
        $this->assertStringContainsString('variant=captions-de.vtt', $list[0]['url']);
        $this->assertStringContainsString('v=' . strtotime($list[0]['updatedAt']), $list[0]['url']);
        $this->assertStringContainsString('guid=' . $file->guid, $list[0]['url']);

        $this->assertSame(array_column($list, 'name'), array_column(FileSerializer::variants($file), 'name'));
    }

    public function testAModuleCanRegisterAWritableType()
    {
        $this->assertFalse(FileVariants::isWritable('notes-1.txt'));

        FileVariants::registerType('notes', [
            'pattern' => '/^notes-\d\.txt$/',
            'mimeTypes' => ['video/'],
            'maxSize' => 100,
            'contentType' => 'text/plain; charset=utf-8',
        ]);

        $file = $this->prepareFile('video/mp4');
        $this->assertSame([], (new FileVariants($file))->put('notes-1.txt', 'hi'));
        $this->assertSame('text/plain; charset=utf-8', FileVariants::getContentType('notes-1.txt'));
    }

    private function prepareFile(string $mimeType): File
    {
        self::becomeUser('Admin');

        $file = new File();
        $file->file_name = 'media.bin';
        $file->mime_type = $mimeType;
        $file->save();

        Post::findOne(['id' => 1])->fileManager->attach($file);
        $file->setStoredFileContent('V0');

        return $file;
    }
}

/**
 * A storage that doesn't keep what it is told to write
 */
class SilentStorageManager extends \humhub\modules\file\components\StorageManager
{
    public function setContent($content, ?string $variant = null): void
    {
    }
}
