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
        $this->assertSame(['old-*', 'subtitles-*'], Yii::$app->getModule('file')->persistentVariants);
    }

    public function testParseName()
    {
        $this->assertSame(['type' => 'subtitles', 'language' => 'de'], FileVariants::parseName('subtitles-de.vtt'));
        $this->assertSame(['type' => 'subtitles', 'language' => 'pt-BR'], FileVariants::parseName('subtitles-pt-BR.vtt'));
        $this->assertSame(['type' => 'subtitles', 'language' => 'zh-Hant'], FileVariants::parseName('subtitles-zh-Hant.vtt'));

        foreach (['subtitles-d.vtt', 'subtitles-DE.vtt', 'subtitles-de.txt', 'subtitles-de.vtt.php', 'subtitles-.vtt', 'old-1', 'preview-image', 'file'] as $name) {
            $this->assertNull(FileVariants::parseName($name), $name);
        }
    }

    public function testPutStoresTheVariantAndItsEntry()
    {
        $file = $this->prepareFile('video/mp4');
        $variants = new FileVariants($file);

        $this->assertSame([], $variants->put('subtitles-de.vtt', self::VTT, 'captions'));

        $this->assertSame(self::VTT, $file->store->getContent('subtitles-de.vtt'));
        $this->assertTrue($variants->has('subtitles-de.vtt'));
        $entry = $variants->getEntries()['subtitles-de.vtt'];
        $this->assertSame('captions', $entry['kind']);
        $this->assertFalse($entry['generated']);
        $this->assertSame(strlen(self::VTT), $entry['size']);
        $this->assertEqualsWithDelta(time(), $entry['updatedAt'], 5);

        // The entry is in the stored metadata
        $reloaded = File::findOne(['id' => $file->id]);
        $this->assertSame('captions', (new FileVariants($reloaded))->getEntries()['subtitles-de.vtt']['kind']);
    }

    public function testPutConvertsSrt()
    {
        $file = $this->prepareFile('audio/mpeg');

        $errors = (new FileVariants($file))->put('subtitles-en.vtt', "1\r\n00:00:01,000 --> 00:00:02,000\r\nHi\r\n", 'subtitles');

        $this->assertSame([], $errors);
        $this->assertSame("WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nHi\n", $file->store->getContent('subtitles-en.vtt'));
    }

    public function testPutRefusesInvalidInput()
    {
        $media = $this->prepareFile('video/mp4');
        $variants = new FileVariants($media);

        $this->assertArrayHasKey('name', $variants->put('subtitles-xx!.vtt', self::VTT, 'subtitles'));
        $this->assertArrayHasKey('name', $variants->put('other.vtt', self::VTT, 'subtitles'));
        $this->assertArrayHasKey('kind', $variants->put('subtitles-de.vtt', self::VTT, 'karaoke'));

        $errors = $variants->put('subtitles-de.vtt', "WEBVTT\n\n00:00:01.000 --> 00:00:03,500\nHi", 'subtitles');
        $this->assertStringStartsWith('Line 3:', $errors['content'][0]);

        $this->assertArrayHasKey('content', $variants->put('subtitles-de.vtt', "WEBVTT\n\n" . str_repeat('x', 1048576), 'subtitles'));
        $this->assertArrayHasKey('content', $variants->put('subtitles-de.vtt', "WEBVTT\n\n00:00:01.000 --> 00:00:03.500\n\xFF", 'subtitles'));

        $this->assertSame([], $variants->getEntries());
        $this->assertFalse($media->store->has('subtitles-de.vtt'));

        $text = $this->prepareFile('text/plain');
        $this->assertArrayHasKey('name', (new FileVariants($text))->put('subtitles-de.vtt', self::VTT, 'subtitles'));
        $this->assertFalse($text->store->has('subtitles-de.vtt'));
    }

    public function testDeleteRemovesOnlyThatVariant()
    {
        $file = $this->prepareFile('video/mp4');
        $variants = new FileVariants($file);
        $variants->put('subtitles-de.vtt', self::VTT, 'subtitles');
        $variants->put('subtitles-en.vtt', self::VTT, 'subtitles');

        $this->assertTrue($variants->delete('subtitles-de.vtt'));

        $this->assertFalse($file->store->has('subtitles-de.vtt'));
        $this->assertTrue($file->store->has('subtitles-en.vtt'));
        $this->assertTrue($file->store->has());
        $this->assertSame(['subtitles-en.vtt'], array_keys($variants->getEntries()));
        $this->assertFalse($variants->delete('subtitles-de.vtt'));

        $variants->delete('subtitles-en.vtt');
        $this->assertNull(File::findOne(['id' => $file->id])->getAttribute('metadata'));
    }

    public function testVariantsSurviveNewContent()
    {
        $file = $this->prepareFile('video/mp4');
        $file->setStoredFileContent('V1');
        (new FileVariants($file))->put('subtitles-de.vtt', self::VTT, 'subtitles');
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
        (new FileVariants($file))->put('subtitles-de.vtt', self::VTT, 'subtitles');
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

    public function testTooManyVariantsAreRefusedBeforeStoring()
    {
        $file = $this->prepareFile('video/mp4');
        $variants = new FileVariants($file);
        $limit = FileMetadata::getMaxLength();

        $last = null;
        foreach (range('a', 'z') as $a) {
            foreach (range('a', 'z') as $b) {
                $name = 'subtitles-' . $a . $b . '.vtt';
                $errors = $variants->put($name, self::VTT, 'subtitles');
                if ($errors !== []) {
                    $last = $name;
                    break 2;
                }
            }
        }

        $this->assertNotNull($last, 'the limit is reached');
        $this->assertArrayHasKey('name', $errors);
        $this->assertFalse($file->store->has($last), 'nothing is stored beyond the limit');
        $this->assertGreaterThan(30, count($variants->getEntries()));

        $reloaded = File::findOne(['id' => $file->id]);
        $this->assertLessThanOrEqual($limit, strlen((string)$reloaded->getAttribute('metadata')));
        $this->assertSame(count($variants->getEntries()), count((new FileVariants($reloaded))->getEntries()));
        $this->assertFalse((new FileVariants($reloaded))->has($last));
    }

    public function testFailedWriteSavesNoEntry()
    {
        $module = Yii::$app->getModule('file');
        $class = $module->storageManagerClass;
        $file = $this->prepareFile('video/mp4');

        $module->storageManagerClass = SilentStorageManager::class;
        try {
            $file = File::findOne(['id' => $file->id]);
            (new FileVariants($file))->put('subtitles-de.vtt', self::VTT, 'subtitles');
            $this->fail('An exception is expected');
        } catch (\yii\base\Exception) {
        } finally {
            $module->storageManagerClass = $class;
        }

        $this->assertSame([], (new FileVariants(File::findOne(['id' => $file->id])))->getEntries());
    }

    public function testAStaleRecordKeepsWhatAnotherWriterStored()
    {
        $file = $this->prepareFile('video/mp4');
        $stale = File::findOne(['id' => $file->id]);

        (new FileVariants($file))->put('subtitles-de.vtt', self::VTT, 'subtitles');
        FileMetadata::update($file, static function (Metadata $metadata): void {
            $metadata->duration = 12.5;
        });

        // The stale record writes its own keys and variant
        FileMetadata::update($stale, static function (Metadata $metadata): void {
            $metadata->width = 640;
        });
        (new FileVariants($stale))->put('subtitles-en.vtt', self::VTT, 'subtitles');

        $metadata = File::findOne(['id' => $file->id])->getMetadata();
        $this->assertSame(12.5, $metadata->duration);
        $this->assertSame(640, $metadata->width);
        $reloaded = new FileVariants(File::findOne(['id' => $file->id]));
        $this->assertSame(['subtitles-de.vtt', 'subtitles-en.vtt'], array_keys($reloaded->getEntries()));
    }

    public function testEntriesInTheKeyedFormAreRead()
    {
        $file = $this->prepareFile('video/mp4');
        $file->updateAttributes(['metadata' => (string)new Metadata(['variants' => [
            'subtitles-de.vtt' => ['kind' => 'captions', 'generated' => false, 'size' => 5, 'updatedAt' => 100],
        ]])]);
        $file = File::findOne(['id' => $file->id]);

        $this->assertSame(
            ['kind' => 'captions', 'generated' => false, 'size' => 5, 'updatedAt' => 100],
            (new FileVariants($file))->getEntries()['subtitles-de.vtt'],
        );
    }

    public function testLabelsOfRegionalLanguages()
    {
        $file = $this->prepareFile('video/mp4');
        $variants = new FileVariants($file);
        foreach (['pt-BR', 'zh-Hant', 'zh-Hans'] as $language) {
            $variants->put('subtitles-' . $language . '.vtt', self::VTT, 'subtitles');
        }

        $labels = array_column(FileSerializer::variants($file), 'label', 'language');

        $this->assertSame('Português (Brasil)', $labels['pt-BR']);
        $this->assertNotSame($labels['zh-Hant'], $labels['zh-Hans']);
    }

    public function testSerializedVariant()
    {
        $file = $this->prepareFile('video/mp4');
        $variants = new FileVariants($file);
        $variants->put('subtitles-pt-BR.vtt', self::VTT, 'captions');
        $variants->put('subtitles-de.vtt', self::VTT, 'subtitles');
        $variants->put('subtitles-en.vtt', self::VTT, 'subtitles');

        $list = FileSerializer::variants($file);

        // By label: Deutsch, English, Português (Brasil)
        $this->assertSame(['subtitles-de.vtt', 'subtitles-en.vtt', 'subtitles-pt-BR.vtt'], array_column($list, 'name'));
        $this->assertSame('Deutsch', $list[0]['label']);
        $this->assertSame('Português (Brasil)', $list[2]['label']);
        $this->assertSame([
            'name' => 'subtitles-pt-BR.vtt',
            'type' => 'subtitles',
            'language' => 'pt-BR',
            'label' => 'Português (Brasil)',
            'kind' => 'captions',
            'generated' => false,
            'size' => strlen(self::VTT),
        ], array_diff_key($list[2], ['updatedAt' => 0, 'url' => 0]));
        $this->assertMatchesRegularExpression('/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d\+00:00$/', $list[2]['updatedAt']);
        $this->assertStringContainsString('variant=subtitles-pt-BR.vtt', $list[2]['url']);
        $this->assertStringContainsString('v=' . strtotime($list[2]['updatedAt']), $list[2]['url']);
        $this->assertStringContainsString('guid=' . $file->guid, $list[2]['url']);
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
