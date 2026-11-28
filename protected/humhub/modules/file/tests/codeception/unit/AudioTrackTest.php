<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use getID3;
use humhub\modules\file\converter\AudioTrack;
use humhub\modules\file\models\File;
use humhub\modules\file\Module;
use humhub\modules\file\serializers\FileSerializer;
use humhub\modules\post\models\Post;
use Symfony\Component\Process\ExecutableFinder;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\log\Logger;

class AudioTrackTest extends HumHubDbTestCase
{
    private ?string $ffmpegPathBefore = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ffmpegPathBefore = $this->getFileModule()->ffmpegPath;
    }

    protected function tearDown(): void
    {
        $this->getFileModule()->ffmpegPath = $this->ffmpegPathBefore;
        parent::tearDown();
    }

    public function testNotApplicableWithoutFfmpeg()
    {
        $this->getFileModule()->ffmpegPath = null;
        $file = $this->createFile('clip.mp4', 'video/mp4', 'with-audio.mp4');

        $track = new AudioTrack();
        $this->assertFalse($track->applyFile($file));
        $this->assertFalse($track->prepare());
        $this->assertFalse($file->store->has(AudioTrack::VARIANT));
    }

    public function testAppliesToVideoAndAudioOnly()
    {
        $this->getFileModule()->ffmpegPath = '/usr/bin/ffmpeg';

        $this->assertTrue((new AudioTrack())->applyFile($this->createFile('clip.mp4', 'video/mp4', 'with-audio.mp4')));
        $this->assertTrue((new AudioTrack())->applyFile($this->createFile('tone.wav', 'audio/wav', 'tone.wav')));
        $this->assertFalse((new AudioTrack())->applyFile($this->createFile('notes.txt', 'text/plain', null, 'text')));
        $this->assertFalse((new AudioTrack())->applyFile($this->createFile('doc.pdf', 'application/pdf', null, 'pdf')));
    }

    public function testAVideoGetsItsSpeechAudioOnRequestOnly()
    {
        $this->requireFfmpeg();
        $file = $this->createFile('talk.mp4', 'video/mp4', 'with-audio.mp4');

        $this->assertFalse($file->store->has(AudioTrack::VARIANT), 'Not created when the content is stored');

        $track = new AudioTrack();
        $this->assertTrue($track->applyFile($file));
        $this->assertTrue($track->prepare());

        $this->assertSpeechAudio($file);
        $this->assertSame(AudioTrack::VARIANT, $track->getFilename());
        $this->assertStringContainsString('variant=' . AudioTrack::VARIANT, $track->getUrl());
    }

    public function testAnAudioFileGetsIt()
    {
        $this->requireFfmpeg();
        $file = $this->createFile('song.mp3', 'audio/mpeg', 'cover.mp3');

        $track = new AudioTrack();
        $this->assertTrue($track->applyFile($file));
        $this->assertTrue($track->prepare());

        $this->assertSpeechAudio($file);
    }

    public function testItIsADerivedVariantInTheList()
    {
        $this->requireFfmpeg();
        $file = $this->createFile('talk.mp4', 'video/mp4', 'with-audio.mp4');
        $this->assertTrue((new AudioTrack(['file' => $file]))->prepare());

        $variants = array_column(FileSerializer::variants($file), null, 'name');
        $this->assertArrayHasKey(AudioTrack::VARIANT, $variants);
        $this->assertFalse($variants[AudioTrack::VARIANT]['persistent']);
        $this->assertSame('audio/ogg', $variants[AudioTrack::VARIANT]['mimeType']);
    }

    public function testItIsDroppedWithNewContent()
    {
        $this->requireFfmpeg();
        $file = $this->createFile('talk.mp4', 'video/mp4', 'with-audio.mp4');
        $this->assertTrue((new AudioTrack(['file' => $file]))->prepare());

        $file->setStoredFileContent(file_get_contents($this->fixture('with-audio.mp4')));

        $this->assertFalse($file->store->has(AudioTrack::VARIANT));
    }

    public function testAVideoWithoutAudioGetsNoneAndIsLogged()
    {
        $this->requireFfmpeg();
        $file = $this->createFile('silent.mp4', 'video/mp4', 'landscape.mp4');

        static::logInitialize();
        $this->assertFalse((new AudioTrack(['file' => $file]))->prepare());

        $this->assertFalse($file->store->has(AudioTrack::VARIANT));
        static::assertLogRegexCount(1, '/^Could not create the audio track of file ' . $file->id . ': /', Logger::LEVEL_WARNING, ['file']);
    }

    public function testAMissingBinaryGivesNoneAndIsLogged()
    {
        $this->getFileModule()->ffmpegPath = '/nonexistent/ffmpeg';
        $file = $this->createFile('talk.mp4', 'video/mp4', 'with-audio.mp4');

        static::logInitialize();
        $this->assertFalse((new AudioTrack(['file' => $file]))->prepare());

        $this->assertFalse($file->store->has(AudioTrack::VARIANT));
        static::assertLogRegexCount(1, '/^Could not create the audio track of file ' . $file->id . ': /', Logger::LEVEL_WARNING, ['file']);
    }

    public function testARemoteFileIsConvertedFromATemporaryCopy()
    {
        $this->requireFfmpeg();
        $file = $this->createFile('talk.mp4', 'video/mp4', 'with-audio.mp4');

        $track = new RemoteAudioTrack(['file' => $file]);
        $this->assertTrue($track->prepare());

        $this->assertSpeechAudio($file);
        $this->assertCount(2, $track->paths);
        $this->assertStringNotContainsString($file->guid, $track->paths[0], 'Read from a copy, not the store');
        $this->assertFileDoesNotExist($track->paths[0]);
        $this->assertFileDoesNotExist($track->paths[1]);
    }

    public function testTheTemporaryFilesAreDeletedWhenItFails()
    {
        $this->requireFfmpeg();
        $file = $this->createFile('silent.mp4', 'video/mp4', 'landscape.mp4');

        $track = new RemoteAudioTrack(['file' => $file]);
        $this->assertFalse($track->prepare());

        $this->assertCount(2, $track->paths);
        $this->assertFileDoesNotExist($track->paths[0]);
        $this->assertFileDoesNotExist($track->paths[1]);
    }

    /**
     * Ogg/Opus, mono, 16 kHz (the input rate in the Opus header: an Opus stream always decodes at 48 kHz)
     */
    private function assertSpeechAudio(File $file): void
    {
        $this->assertTrue($file->store->has(AudioTrack::VARIANT));

        $path = tempnam(sys_get_temp_dir(), 'audio');
        file_put_contents($path, $file->store->getContent(AudioTrack::VARIANT));
        try {
            $info = (new getID3())->analyze($path);
        } finally {
            unlink($path);
        }

        $this->assertSame('ogg', $info['fileformat'] ?? null);
        $this->assertSame('opus', $info['audio']['dataformat'] ?? null);
        $this->assertSame(1, $info['audio']['channels'] ?? null);
        $this->assertSame(16000, $info['audio']['sample_rate_input'] ?? null);
        $this->assertEqualsWithDelta(1.0, $info['playtime_seconds'] ?? 0, 0.1);
    }

    private function requireFfmpeg(): void
    {
        $binary = (new ExecutableFinder())->find('ffmpeg');
        if ($binary === null) {
            $this->markTestSkipped('ffmpeg is not installed.');
        }

        $this->getFileModule()->ffmpegPath = $binary;
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

    private function getFileModule(): Module
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('file');

        return $module;
    }
}

/**
 * A converter whose files are not on a local path; it remembers the input and output paths of ffmpeg
 */
class RemoteAudioTrack extends AudioTrack
{
    public array $paths = [];

    protected function openStream()
    {
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, $this->file->store->getContent());
        rewind($stream);

        return $stream;
    }

    protected function buildCommand(string $input, string $output): array
    {
        $this->paths = [$input, $output];

        return parent::buildCommand($input, $output);
    }
}
