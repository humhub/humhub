<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

use humhub\modules\user\models\User;
use Throwable;

interface ViewableInterface
{
    /**
     * Checks if user can view this element.
     *
     * @param User|integer $user
     *
     * @return boolean can view this element
     * @throws Throwable
     * @since 1.15
     */
    public function canView($user = null);

}
