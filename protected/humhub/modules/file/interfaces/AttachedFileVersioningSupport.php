<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\interfaces;

/**
 * Interface for active records which support File Versioning.
 *
 * @see \humhub\modules\file\behaviors\Versions
 * @author luke
 * @since 1.10
 */
interface AttachedFileVersioningSupport
{
    /**
     * Change current version to new by File ID
     *
     * @param int $newFileID
     * @return bool
     */
    public function changeVersionByFileId(int $newFileID): bool;

    /**
     * Refresh all old/previous version files related to this active record to new currently active File
     * (Used to relink all old/previous versions to new created File right after insertion)
     *
     * @return bool
     */
    public function refreshVersions(): bool;
}