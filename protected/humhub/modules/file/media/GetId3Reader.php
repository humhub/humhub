<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\media;

use getID3;
use Throwable;

/**
 * Pure PHP reader based on getID3
 *
 * @since 1.20
 */
class GetId3Reader implements MediaInfoReaderInterface
{
    /**
     * @inheritdoc
     */
    public function read(string $path, string $fileName = ''): ?MediaInfo
    {
        try {
            $getId3 = new getID3();
            $getId3->option_md5_data = false;
            $getId3->option_md5_data_source = false;
            $getId3->option_sha1_data = false;
            $getId3->option_extra_info = false;
            $getId3->option_save_attachments = false;

            // With the extension it reads an MP3 whose frames don't start at the beginning (e.g. after padding)
            $info = $getId3->analyze($path, null, $fileName);

            $width = $height = null;
            $isAudio = str_starts_with((string)($info['mime_type'] ?? ''), 'audio/');
            $x = $info['video']['resolution_x'] ?? null;
            $y = $info['video']['resolution_y'] ?? null;
            // The picture of an audio file's cover is no video size
            if (!$isAudio && is_numeric($x) && is_numeric($y) && $x > 0 && $y > 0) {
                $width = (int)$x;
                $height = (int)$y;
                if (in_array(abs((int)round((float)($info['video']['rotate'] ?? 0))) % 360, [90, 270], true)) {
                    [$width, $height] = [$height, $width];
                }
            }

            $duration = isset($info['playtime_seconds']) && is_numeric($info['playtime_seconds']) && $info['playtime_seconds'] > 0
                ? (float)$info['playtime_seconds']
                : null;

            if ($width === null && $duration === null) {
                return null;
            }

            return new MediaInfo($width, $height, $duration);
        } catch (Throwable) {
            return null;
        }
    }
}
