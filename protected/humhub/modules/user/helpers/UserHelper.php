<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\helpers;

use humhub\modules\user\models\User;
use Yii;

final class UserHelper
{
    public static function getUserByParam(User|int|null $idOrUser = null, bool $onNullCurrentUser = true): ?User
    {
        if ($idOrUser instanceof User) {
            return $idOrUser;
        }

        if ($idOrUser === null && $onNullCurrentUser) {
            return (Yii::$app->user->isGuest) ? null : Yii::$app->getUser()->getIdentity();
        }

        if (is_int($idOrUser) || ctype_digit($idOrUser)) {
            return User::findOne(['id' => (int)$idOrUser]);
        }

        return null;
    }
}
