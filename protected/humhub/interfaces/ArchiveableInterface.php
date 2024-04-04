<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

use humhub\modules\user\models\User;

/**
 * Deletable Interface
 * @since 1.16
 */
interface ArchiveableInterface
{
    /**
     * Checks if the given user can edit/create this element.
     *
     * @param User|int|string|null $user user instance or user id
     * @return bool
     */
    public function canArchive($user = null): bool;

    /**
     * Archive this object
     *
     * @return bool
     */
    public function archive(): bool;

    /**
     * Unarchive this object
     *
     * @return bool
     */
    public function unarchive(): bool;
}
