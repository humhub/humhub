<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

use humhub\modules\user\models\User;
use Throwable;

interface EditableInterface
{

    /**
     * Checks if the given user can edit/create this element.
     *
     * @param User|integer $user user instance or user id
     *
     * @return bool can edit/create this element
     * @since 1.15
     */
    public function canEdit($user = null): bool;

}
