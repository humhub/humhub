<?php

namespace humhub\interfaces;

use humhub\modules\user\models\User;

/**
 * Deletable Interface
 * @since 1.16
 */
interface DeletableInterface
{
    /**
     * Checks if given item can be deleted.
     *
     * @param User|int|string|null $user user instance or user id
     * @return bool
     */
    public function canDelete($user = null): bool;

    /**
     * Delete this object
     */
    public function delete();
}
