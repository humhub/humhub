<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\services;

use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\User;
use Yii;

/**
 * @since 1.14
 */
class MemberListService
{
    private Space $space;

    public function __construct(Space $space)
    {
        $this->space = $space;
    }

    public function getAdminQuery(): ActiveQueryUser
    {
        return Membership::getSpaceMembersQuery($this->space);
    }

    public function getNotificationQuery(bool $withNotifications = true): ActiveQueryUser
    {
        return Membership::getSpaceMembersQuery($this->space, true, $withNotifications);
    }

    public function getReadableQuery(?User $user = null): ActiveQueryUser
    {
        $query = Membership::getSpaceMembersQuery($this->space);

        if (Yii::$app->user->isGuest) {
            return $query->active()
                ->andWhere(['!=', 'user.visibility', User::VISIBILITY_HIDDEN]);
        }

        return $query->visible($user);
    }

    public function getQuery(?User $user = null): ActiveQueryUser
    {
        return Membership::getSpaceMembersQuery($this->space)->visible($user);
    }

    public function getAvailableQuery(?User $user = null): ActiveQueryUser
    {
        return $this->getQuery($user)->filterBlockedUsers($user);
    }

    public function getCount(?User $user = null): int
    {
        return $this->getQuery($user)->count();
    }
}
