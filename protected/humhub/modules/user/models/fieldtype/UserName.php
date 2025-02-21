<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\libs\Html;
use humhub\modules\user\models\User;

/**
 * UserName is a virtual profile field
 * that displays the current user name of the user.
 *
 * @since 1.6
 */
class UserName extends BaseTypeVirtual
{
    /**
     * @inheritDoc
     */
    public function getVirtualUserValue(User $user, bool $raw = true, bool $encode = true): string
    {
        if (empty($user->username)) {
            return '';
        }

        return $encode ? Html::encode($user->username) : $user->username;
    }
}
