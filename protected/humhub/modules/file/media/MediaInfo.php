<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\media;

/**
 * Metadata of a video or audio file
 *
 * @since 1.20
 */
final readonly class MediaInfo
{
    /**
     * @param int|null $width Width as shown (rotation applied), videos only
     * @param int|null $height Height as shown (rotation applied), videos only
     * @param float|null $duration Duration in seconds
     */
    public function __construct(
        public ?int $width = null,
        public ?int $height = null,
        public ?float $duration = null,
    ) {
    }
}
