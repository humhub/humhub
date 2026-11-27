<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\file\jobs\CollectFileMetadata;
use humhub\modules\file\jobs\CollectMediaInfo;
use humhub\modules\file\libs\ImageHelper;
use humhub\modules\file\libs\MediaHelper;
use humhub\modules\file\libs\Metadata;
use humhub\modules\file\media\GetId3Reader;
use humhub\modules\file\media\MediaInfo;
use humhub\modules\file\media\MediaInfoReaderInterface;
use humhub\modules\file\models\File;
use humhub\modules\file\Module;
use humhub\modules\file\serializers\FileSerializer;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\queue\PushEvent;
use yii\queue\Queue;

class MediaHelperTest extends HumHubDbTestCase
{
    protected function _after()
    {
        $this->getFileModule()->mediaInfoReader = null;
        parent::_after();
    }

    public function testAVideoGetsItsSizeAndDuration()
    {
        $file = $this->createFile('clip.mp4', 'video/mp4', 'landscape.mp4');

        $info = MediaHelper::getMediaInfo($file);
        $this->assertSame(64, $info->width);
        $this->assertSame(36, $info->height);
        $this->assertEqualsWithDelta(1.0, $info->duration, 0.1);
        $this->assertSame(round($info->duration, 2), $info->duration);
    }

    public function testASizeFollowsTheDisplayRotation()
    {
        $file = $this->createFile('clip.mp4', 'video/mp4', 'rotated.mp4');

        $info = MediaHelper::getMediaInfo($file);
        $this->assertSame(36, $info->width);
        $this->assertSame(64, $info->height);
    }

    public function testAudioGetsOnlyADuration()
    {
        $file = $this->createFile('song.mp3', 'audio/mpeg', 'cover.mp3');

        $info = MediaHelper::getMediaInfo($file);
        $this->assertNull($info->width);
        $this->assertNull($info->height);
        $this->assertEqualsWithDelta(1.04, $info->duration, 0.1);
        $this->assertFalse($file->getMetadata()->offsetExists('width'));
    }

    public function testNewContentOfAnotherTypeDropsTheStaleValues()
    {
        $file = $this->createFile('clip.mp4', 'video/mp4', 'landscape.mp4');
        $file->setMetadata(['origin' => 'import']);
        $file->updateAttributes(['metadata' => (string)$file->getMetadata()]);

        $file = File::findOne($file->id);
        $file->updateAttributes(['mime_type' => 'text/plain']);
        $file->setStoredFileContent('plain text');

        $file = File::findOne($file->id);
        $this->assertNull(MediaHelper::getMediaInfo($file));
        $this->assertSame('import', $file->getMetadata()->origin);

        // Nothing else left: no metadata at all
        $file->updateAttributes(['metadata' => null]);
        $video = $this->createFile('clip.mp4', 'video/mp4', 'landscape.mp4');
        $video->updateAttributes(['mime_type' => 'text/plain']);
        $video->setStoredFileContent('plain text');
        $this->assertNull(File::findOne($video->id)->getAttribute('metadata'));
    }

    public function testAnImageKeepsItsDimensionsAndGetsNoDuration()
    {
        $file = $this->createFile('photo.jpg', 'image/jpeg', null, $this->createJpeg(200, 100));
        $file->setMetadata(['duration' => 3.5]);
        $file->updateAttributes(['metadata' => (string)$file->getMetadata()]);

        $file = File::findOne($file->id);
        MediaHelper::collectMediaInfo($file);

        $file = File::findOne($file->id);
        $this->assertSame([200, 100], ImageHelper::getDimensions($file));
        $this->assertNull(MediaHelper::getDuration($file));
    }

    public function testImageHelperKeepsTheSizeOfAVideo()
    {
        $file = $this->createFile('clip.mp4', 'video/mp4', 'landscape.mp4');

        ImageHelper::collectDimensions($file);

        $this->assertSame([64, 36], ImageHelper::getDimensions(File::findOne($file->id)));
    }

    public function testGapsOfTheModuleReaderAreFilledByGetId3()
    {
        // As ffprobe does for a file it reads a duration of only
        $this->getFileModule()->mediaInfoReader = $this->reader(fn() => new MediaInfo(null, null, 2.0));

        $file = $this->createFile('clip.mp4', 'video/mp4', 'rotated.mp4');

        $info = MediaHelper::getMediaInfo($file);
        $this->assertSame(36, $info->width);
        $this->assertSame(64, $info->height);
        $this->assertSame(2.0, $info->duration); // not overwritten
    }

    public function testAReaderWithoutResultIsReplacedByGetId3()
    {
        $this->getFileModule()->mediaInfoReader = $this->reader(fn() => null);

        $file = $this->createFile('song.mp3', 'audio/mpeg', 'cover.mp3');

        $this->assertEqualsWithDelta(1.04, MediaHelper::getDuration($file), 0.1);
    }

    public function testAThrowingReaderIsLoggedNotThrown()
    {
        $this->getFileModule()->mediaInfoReader = $this->reader(function () {
            throw new \RuntimeException('ffprobe crashed');
        });

        $file = $this->createFile('clip.mp4', 'video/mp4', 'landscape.mp4');

        $this->assertSame(64, MediaHelper::getMediaInfo($file)->width); // from getID3
    }

    public function testAFileThatCantBeOpenedIsLoggedNotThrown()
    {
        $file = $this->createFile('clip.mp4', 'video/mp4', 'landscape.mp4');
        $before = $file->getAttribute('metadata');

        $file = File::findOne($file->id);
        $file->store->delete();
        MediaHelper::collectMediaInfo($file);

        $this->assertSame($before, File::findOne($file->id)->getAttribute('metadata'));
    }

    public function testARemoteFileIsReadInTheBackground()
    {
        $file = $this->createFile('clip.mp4', 'video/mp4', 'landscape.mp4');
        $file->updateAttributes(['metadata' => null]);

        $jobs = $this->capturePushedJobs(fn() => RemoteMediaHelper::collectMediaInfo($file));

        $this->assertCount(1, $jobs);
        $this->assertInstanceOf(CollectMediaInfo::class, $jobs[0]);
        $this->assertSame($file->id, $jobs[0]->fileId);
        $this->assertNull(File::findOne($file->id)->getAttribute('metadata'));
    }

    public function testARemoteFileDropsTheValuesOfThePreviousContentUntilTheJobRan()
    {
        $file = $this->createFile('clip.mp4', 'video/mp4', 'landscape.mp4');
        $file->setMetadata(['width' => 400, 'height' => 300, 'duration' => 5.0]);
        $file->updateAttributes(['metadata' => (string)$file->getMetadata()]);

        $jobs = $this->capturePushedJobs(fn() => RemoteMediaHelper::collectMediaInfo($file));

        $this->assertCount(1, $jobs);
        $this->assertNull(MediaHelper::getMediaInfo(File::findOne($file->id)));
    }

    public function testTheModuleTakesAReaderConfiguration()
    {
        $this->getFileModule()->mediaInfoReader = ['class' => GetId3Reader::class];

        $this->assertInstanceOf(GetId3Reader::class, $this->getFileModule()->getMediaInfoReader());
    }

    public function testARemoteFileIsReadFromATemporaryCopy()
    {
        $file = $this->createFile('clip.mp4', 'video/mp4', 'landscape.mp4');
        $file->updateAttributes(['metadata' => null]);

        $paths = [];
        $this->getFileModule()->mediaInfoReader = $this->reader(function (string $path) use (&$paths) {
            $paths[] = $path;
            $this->assertFileEquals($this->fixture('landscape.mp4'), $path);

            return new MediaInfo(64, 36, 1.0);
        });

        $jobs = $this->capturePushedJobs(fn() => RemoteMediaHelper::collectMediaInfo($file, true));

        $this->assertSame([], $jobs);
        $this->assertCount(1, $paths);
        $this->assertFileDoesNotExist($paths[0]);
        $this->assertSame(64, MediaHelper::getMediaInfo(File::findOne($file->id))->width);
    }

    public function testTheTemporaryCopyIsDeletedWhenReadingFails()
    {
        $file = $this->createFile('clip.mp4', 'video/mp4', 'landscape.mp4');

        $paths = [];
        $this->getFileModule()->mediaInfoReader = $this->reader(function (string $path) use (&$paths) {
            $paths[] = $path;
            throw new \RuntimeException('broken');
        });

        RemoteMediaHelper::collectMediaInfo($file, true);

        $this->assertNotEmpty($paths);
        $this->assertFileDoesNotExist($paths[0]);
    }

    public function testReadingTheStoredMetadata()
    {
        $file = $this->createFile('notes.txt', 'text/plain', null, 'text');
        $this->assertNull(MediaHelper::getMediaInfo($file));
        $this->assertNull(MediaHelper::getDuration($file));

        $file->updateAttributes(['metadata' => 'garbage']);
        $this->assertNull(MediaHelper::getMediaInfo(File::findOne($file->id)));

        $file->updateAttributes(['metadata' => (string)new Metadata(['origin' => 'import'])]);
        $this->assertNull(MediaHelper::getMediaInfo(File::findOne($file->id)));

        $file->updateAttributes(['metadata' => (string)new Metadata(['duration' => 12.5])]);
        $info = MediaHelper::getMediaInfo(File::findOne($file->id));
        $this->assertNull($info->width);
        $this->assertSame(12.5, $info->duration);
        $this->assertSame(12.5, MediaHelper::getDuration(File::findOne($file->id)));

        $file->updateAttributes(['metadata' => (string)new Metadata(['width' => 64, 'height' => 36, 'duration' => 1.0])]);
        $info = MediaHelper::getMediaInfo(File::findOne($file->id));
        $this->assertSame([64, 36, 1.0], [$info->width, $info->height, $info->duration]);
    }

    public function testTheJobCollectsMediaWithoutADurationAndSkipsTheOthers()
    {
        $video = $this->createFile('clip.mp4', 'video/mp4', 'landscape.mp4');
        $audio = $this->createFile('song.mp3', 'audio/mpeg', 'cover.mp3');
        $done = $this->createFile('done.mp4', 'video/mp4', 'landscape.mp4');
        $video->updateAttributes(['metadata' => null]); // as uploaded before 1.20
        $audio->updateAttributes(['metadata' => (string)new Metadata(['origin' => 'import'])]);

        $seen = [];
        CollectFileMetadata::collect(function (File $f) use (&$seen) {
            $seen[] = $f->id;
        });

        $this->assertContains($video->id, $seen);
        $this->assertContains($audio->id, $seen);
        $this->assertNotContains($done->id, $seen);
        $this->assertSame(64, MediaHelper::getMediaInfo(File::findOne($video->id))->width);
        $audio = File::findOne($audio->id);
        $this->assertEqualsWithDelta(1.04, MediaHelper::getDuration($audio), 0.1);
        $this->assertSame('import', $audio->getMetadata()->origin);
    }

    public function testTheMediaJobCollectsAFile()
    {
        $file = $this->createFile('clip.mp4', 'video/mp4', 'landscape.mp4');
        $file->updateAttributes(['metadata' => null]);

        (new CollectMediaInfo(['fileId' => $file->id]))->run();
        $this->assertSame(64, MediaHelper::getMediaInfo(File::findOne($file->id))->width);

        // A file that is gone, or has no content, is skipped
        (new CollectMediaInfo(['fileId' => 999999]))->run();
        $orphan = new File();
        $orphan->file_name = 'orphan.mp4';
        $orphan->mime_type = 'video/mp4';
        $this->assertTrue($orphan->save());
        (new CollectMediaInfo(['fileId' => $orphan->id]))->run();
        $this->assertNull(MediaHelper::getMediaInfo(File::findOne($orphan->id)));
    }

    public function testTheFileShapeCarriesTheDuration()
    {
        $video = $this->createFile('clip.mp4', 'video/mp4', 'rotated.mp4');
        $audio = $this->createFile('song.mp3', 'audio/mpeg', 'cover.mp3');
        $text = $this->createFile('notes.txt', 'text/plain', null, 'text');

        $shape = FileSerializer::file($video);
        $this->assertSame([36, 64], [$shape['width'], $shape['height']]);
        $this->assertEqualsWithDelta(1.0, $shape['duration'], 0.1);

        $shape = FileSerializer::file($audio);
        $this->assertNull($shape['width']);
        $this->assertEqualsWithDelta(1.04, $shape['duration'], 0.1);

        $this->assertNull(FileSerializer::file($text)['duration']);
    }

    private function fixture(string $name): string
    {
        return __DIR__ . '/../_data/media/' . $name;
    }

    /**
     * @param string|null $fixture a file of `_data/media` as content
     */
    private function createFile(string $fileName, string $mimeType, ?string $fixture, ?string $content = null): File
    {
        $file = new File();
        $file->file_name = $fileName;
        $file->mime_type = $mimeType;
        $this->assertTrue($file->save());

        Post::findOne(['id' => 1])->fileManager->attach($file);
        $file->setStoredFileContent($content ?? file_get_contents($this->fixture($fixture)));

        return File::findOne($file->id);
    }

    private function createJpeg(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        ob_start();
        imagejpeg($image);

        return ob_get_clean();
    }

    private function reader(callable $read): MediaInfoReaderInterface
    {
        return new class ($read) implements MediaInfoReaderInterface {
            public function __construct(private $read)
            {
            }

            public function read(string $path, string $fileName = ''): ?MediaInfo
            {
                return ($this->read)($path);
            }
        };
    }

    /**
     * @return object[]
     */
    private function capturePushedJobs(callable $callback): array
    {
        $jobs = [];
        $handler = function (PushEvent $event) use (&$jobs): void {
            $jobs[] = $event->job;
            $event->handled = true;
        };
        Yii::$app->queue->on(Queue::EVENT_BEFORE_PUSH, $handler);
        try {
            $callback();
        } finally {
            Yii::$app->queue->off(Queue::EVENT_BEFORE_PUSH, $handler);
        }

        return $jobs;
    }

    private function getFileModule(): Module
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('file');

        return $module;
    }
}

/**
 * A helper whose files are not on a local path
 */
class RemoteMediaHelper extends MediaHelper
{
    protected static function openStream(File $file)
    {
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, $file->store->getContent());
        rewind($stream);

        return $stream;
    }
}
