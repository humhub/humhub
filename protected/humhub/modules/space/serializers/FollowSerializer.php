<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\serializers;

use humhub\modules\space\models\Space;
use humhub\modules\space\Module;
use Yii;

/**
 * The current user's follow relationship to a space, as `space/<id>/follow` answers it and the
 * `FollowButton` island renders it (see `docs/develop/concept-api.md`). Caller context, like
 * {@see MembershipSerializer}.
 *
 * @since 1.20
 */
class FollowSerializer
{
    /**
     * `followerCount` is the space list's count ({@see SpaceSerializer::counts()}): the same for
     * every caller, `null` where the space does not show its followers.
     *
     * @return array{isFollowing: bool, followerCount: int|null, canFollow: bool}
     */
    public static function state(Space $space): array
    {
        return [
            'isFollowing' => !Yii::$app->user->isGuest && (bool)$space->isFollowedByUser(),
            'followerCount' => SpaceSerializer::counts([$space])[$space->id]['followerCount'],
            'canFollow' => self::canFollow($space),
        ];
    }

    /**
     * Whether the current user may start following the space: not a guest, not a member
     * (members cannot follow — joining ends a follow), and following is not disabled.
     * Ending a follow is always possible.
     */
    public static function canFollow(Space $space): bool
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('space');

        return !Yii::$app->user->isGuest && !$module->disableFollow && !$space->isMember();
    }
}
