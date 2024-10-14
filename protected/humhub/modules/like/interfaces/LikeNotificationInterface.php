<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\interfaces;

/**
 * Interface for Active Records that can be liked
 *
 * @since 1.16
 */
interface LikeNotificationInterface
{
    /**
     * Get the plain text preview of the liked object
     */
    public function getLikeNotificationPlainTextPreview(): string;

    /**
     * Get the HTML text preview of the liked object
     */
    public function getLikeNotificationHtmlPreview(): string;

    /**
     * Get the URL of the liked object
     */
    public function getLikeNotificationUrl(bool $scheme = false): string;
}
