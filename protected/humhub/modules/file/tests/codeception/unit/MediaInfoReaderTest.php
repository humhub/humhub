<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\file\media\FfprobeReader;
use humhub\modules\file\media\GetId3Reader;
use humhub\modules\file\media\MediaInfo;
use humhub\modules\file\Module;
use Symfony\Component\Process\ExecutableFinder;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class MediaInfoReaderTest extends HumHubDbTestCase
{
    private function fixture(string $name): string
    {
        return __DIR__ . '/../_data/media/' . $name;
    }

    private function assertInfo(?MediaInfo $info, ?int $width, ?int $height, ?float $duration)
    {
        $this->assertNotNull($info);
        $this->assertSame($width, $info->width);
        $this->assertSame($height, $info->height);
        if ($duration === null) {
            $this->assertNull($info->duration);
        } else {
            $this->assertEqualsWithDelta($duration, $info->duration, 0.1);
        }
    }

    public function testGetId3Videos()
    {
        $reader = new GetId3Reader();

        $this->assertInfo($reader->read($this->fixture('landscape.mp4')), 64, 36, 1.0);
        $this->assertInfo($reader->read($this->fixture('rotated.mp4')), 36, 64, 1.0);
        $this->assertInfo($reader->read($this->fixture('clip.webm')), 48, 32, 1.0);
    }

    public function testGetId3AudioHasNoSize()
    {
        $reader = new GetId3Reader();

        $this->assertInfo($reader->read($this->fixture('tone.wav')), null, null, 1.0);
        $this->assertInfo($reader->read($this->fixture('cover.mp3')), null, null, 1.04);
    }

    public function testGetId3ReadsAnMp3WithPaddingByItsFileName()
    {
        // Stored without an extension, as the platform stores files
        $path = sys_get_temp_dir() . '/' . uniqid('media', true);
        copy($this->fixture('padded.mp3'), $path);
        try {
            $reader = new GetId3Reader();

            $this->assertNull($reader->read($path));
            $this->assertInfo($reader->read($path, 'Track 01.mp3'), null, null, 1.0);
        } finally {
            unlink($path);
        }
    }

    public function testGetId3UnreadableFiles()
    {
        $reader = new GetId3Reader();

        $this->assertNull($reader->read($this->fixture('missing.mp4')));
        $this->assertNull($reader->read($this->fixture('README.md')));
    }

    public function testFfprobeParseRotationFromSideData()
    {
        $json = json_encode([
            'streams' => [['codec_type' => 'video', 'width' => 64, 'height' => 36, 'side_data_list' => [['rotation' => -90]]]],
            'format' => ['duration' => '2.500000'],
        ]);

        $this->assertInfo((new FfprobeReader('ffprobe'))->parse($json, ''), 36, 64, 2.5);
    }

    public function testFfprobeParseRotationFromTags()
    {
        $json = json_encode([
            'streams' => [['codec_type' => 'video', 'width' => 64, 'height' => 36, 'tags' => ['rotate' => '270']]],
            'format' => ['duration' => '1.0'],
        ]);
        $this->assertInfo((new FfprobeReader('ffprobe'))->parse($json, ''), 36, 64, 1.0);

        $json = json_encode([
            'streams' => [['codec_type' => 'video', 'width' => 64, 'height' => 36, 'tags' => ['rotate' => '180']]],
            'format' => ['duration' => '1.0'],
        ]);
        $this->assertInfo((new FfprobeReader('ffprobe'))->parse($json, ''), 64, 36, 1.0);
    }

    public function testFfprobeParseSkipsAttachedPicture()
    {
        $json = json_encode([
            'streams' => [
                ['codec_type' => 'audio'],
                ['codec_type' => 'video', 'width' => 500, 'height' => 500, 'disposition' => ['attached_pic' => 1]],
            ],
            'format' => ['duration' => '200.0'],
        ]);

        $this->assertInfo((new FfprobeReader('ffprobe'))->parse($json, ''), null, null, 200.0);
    }

    public function testFfprobeParseIgnoresEstimatedDuration()
    {
        $json = json_encode([
            'streams' => [['codec_type' => 'audio']],
            'format' => ['duration' => '265.0'],
        ]);
        $reader = new FfprobeReader('ffprobe');

        $this->assertNull($reader->parse($json, '[mp3 @ 0x1] Estimating duration from bitrate, this may be inaccurate'));

        $json = json_encode([
            'streams' => [['codec_type' => 'video', 'width' => 64, 'height' => 36]],
            'format' => ['duration' => '265.0'],
        ]);
        $this->assertInfo($reader->parse($json, 'Estimating duration from bitrate'), 64, 36, null);
    }

    public function testFfprobeParseBrokenJson()
    {
        $reader = new FfprobeReader('ffprobe');

        $this->assertNull($reader->parse('{broken', ''));
        $this->assertNull($reader->parse('', ''));
        $this->assertNull($reader->parse('{}', ''));
    }

    public function testFfprobeReadsFixtures()
    {
        $binary = (new ExecutableFinder())->find('ffprobe');
        if ($binary === null) {
            $this->markTestSkipped('ffprobe is not installed.');
        }
        $reader = new FfprobeReader($binary);

        $this->assertInfo($reader->read($this->fixture('landscape.mp4')), 64, 36, 1.0);
        $this->assertInfo($reader->read($this->fixture('rotated.mp4')), 36, 64, 1.0);
        $this->assertInfo($reader->read($this->fixture('clip.webm')), 48, 32, 1.0);
        $this->assertInfo($reader->read($this->fixture('tone.wav')), null, null, 1.0);
        $this->assertInfo($reader->read($this->fixture('cover.mp3')), null, null, 1.04);
        $this->assertNull($reader->read($this->fixture('missing.mp4')));
    }

    public function testFfprobeMissingBinary()
    {
        $this->assertNull((new FfprobeReader('/nonexistent/ffprobe'))->read($this->fixture('landscape.mp4')));
    }

    public function testModulePicksTheReader()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('file');
        $before = $module->ffprobePath;

        try {
            $module->ffprobePath = null;
            $this->assertInstanceOf(GetId3Reader::class, $module->getMediaInfoReader());

            $module->ffprobePath = '/usr/bin/ffprobe';
            $this->assertInstanceOf(FfprobeReader::class, $module->getMediaInfoReader());
        } finally {
            $module->ffprobePath = $before;
        }
    }
}
