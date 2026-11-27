<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\media;

use Symfony\Component\Process\Process;
use Throwable;

/**
 * Reader based on the ffprobe binary
 *
 * @since 1.20
 */
class FfprobeReader implements MediaInfoReaderInterface
{
    private const TIMEOUT = 30;

    public function __construct(private readonly string $binary)
    {
    }

    /**
     * @inheritdoc
     */
    public function read(string $path, string $fileName = ''): ?MediaInfo
    {
        try {
            $process = new Process(
                [$this->binary, '-v', 'warning', '-print_format', 'json', '-show_streams', '-show_format', $path],
                null,
                null,
                null,
                self::TIMEOUT,
            );
            $process->run();

            if (!$process->isSuccessful()) {
                return null;
            }

            return $this->parse($process->getOutput(), $process->getErrorOutput());
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param string $json Output of ffprobe (`-print_format json -show_streams -show_format`)
     * @param string $stderr Warnings of ffprobe (`-v warning`)
     */
    public function parse(string $json, string $stderr): ?MediaInfo
    {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return null;
        }

        $width = $height = null;
        foreach ($data['streams'] ?? [] as $stream) {
            if (($stream['codec_type'] ?? null) !== 'video' || !empty($stream['disposition']['attached_pic'])) {
                continue;
            }

            $w = (int)($stream['width'] ?? 0);
            $h = (int)($stream['height'] ?? 0);
            if ($w > 0 && $h > 0) {
                [$width, $height] = in_array(self::getRotation($stream), [90, 270], true) ? [$h, $w] : [$w, $h];
            }
            break;
        }

        $duration = null;
        $formatDuration = $data['format']['duration'] ?? null;
        if (is_numeric($formatDuration) && $formatDuration > 0 && !str_contains($stderr, 'Estimating duration from bitrate')) {
            $duration = (float)$formatDuration;
        }

        if ($width === null && $duration === null) {
            return null;
        }

        return new MediaInfo($width, $height, $duration);
    }

    /**
     * @return int Display rotation of a video stream, normalized to 0..359
     */
    private static function getRotation(array $stream): int
    {
        $rotation = $stream['tags']['rotate'] ?? 0;
        foreach ($stream['side_data_list'] ?? [] as $sideData) {
            if (isset($sideData['rotation'])) {
                $rotation = $sideData['rotation'];
                break;
            }
        }

        return (((int)round((float)$rotation) % 360) + 360) % 360;
    }
}
