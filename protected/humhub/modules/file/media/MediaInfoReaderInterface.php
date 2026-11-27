<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\media;

/**
 * Reads the metadata of a video or audio file
 *
 * @since 1.20
 */
interface MediaInfoReaderInterface
{
    /**
     * @param string $path Local file path
     * @param string $fileName The file's own name: a stored file has none, a reader may need its extension to tell the
     * format
     * @return MediaInfo|null null when the file can't be read
     */
    public function read(string $path, string $fileName = ''): ?MediaInfo;
}
