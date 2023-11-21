<?php
/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

use humhub\modules\user\models\User;

/**
 * Readable Interface
 * @since 1.16
 */
interface ReadableInterface
{
    /**
     * Checks if given element can be read.
     *
     * @param User|integer|string|null $user User instance or user id, null - current user
     * @return bool
     */
    public function canRead($user = null): bool;
}
