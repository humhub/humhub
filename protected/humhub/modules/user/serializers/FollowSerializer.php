<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\serializers;

use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Yii;

/**
 * The current user's follow relationship to another user, as `user/<id>/follow` answers it
 * (see `docs/develop/concept-api.md`). Caller context, like the space's
 * {@see \humhub\modules\space\serializers\FollowSerializer}.
 *
 * @since 1.20
 */
class FollowSerializer
{
    /**
     * `followerCount` is the user list's count ({@see UserSerializer::counts()}): the same for
     * every caller, `null` while following is disabled.
     *
     * @return array{isFollowing: bool, followerCount: int|null, canFollow: bool}
     */
    public static function state(User $user): array
    {
        return [
            'isFollowing' => !Yii::$app->user->isGuest && (bool)$user->isFollowedByUser(),
            'followerCount' => UserSerializer::counts([$user])[$user->id]['followerCount'],
            'canFollow' => self::canFollow($user),
        ];
    }

    /**
     * Whether the current user may start following the user: not a guest, not themselves, and
     * following is not disabled. Ending a follow is always possible.
     */
    public static function canFollow(User $user): bool
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        return !Yii::$app->user->isGuest && !$module->disableFollow && !$user->isCurrentUser();
    }
}
