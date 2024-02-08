<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

use humhub\modules\user\models\User;

/**
 * Viewable Interface
 * @since 1.16
 */
interface ViewableInterface
{
    /**
     * Checks if user can view this element.
     *
     * @param User|int|string|null $user User instance or user id, null - current user
     * @return bool
     */
    public function canView($user = null): bool;

}
