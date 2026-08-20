<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

use humhub\modules\user\models\User;
use Yii;

/**
 * Serializes a {@see User} into the JSON author/user shape shared by every
 * JSON API that needs to describe a user to a Vue island - the shape
 * `humhub\modules\user\vue\UserImage`'s props are modeled on, so a caller
 * normally just spreads it: `<UserImage v-bind="userJson" />` /
 * `<UserList>` rows.
 *
 * Extracted from `humhub\modules\comment\services\CommentJsonService::serializeAuthor()`
 * (the original, comment-specific implementation of this exact shape) so a second
 * caller - the like module's user-list endpoint - does not have to duplicate it.
 * `CommentJsonService` now delegates here; its own payload is unchanged.
 *
 * @since 1.19
 */
class UserJsonService
{
    /**
     * @return array{
     *     guid: string,
     *     displayName: string,
     *     url: string,
     *     imageUrl: string,
     *     contentContainerId: int,
     *     imageAlt: string,
     *     online: bool|null,
     * }
     */
    public function serialize(User $user): array
    {
        return [
            'guid' => $user->guid,
            'displayName' => $user->displayName,
            'url' => $user->getUrl(),
            'imageUrl' => $user->getProfileImage()->getUrl(),
            'contentContainerId' => $user->contentcontainer_id,
            'imageAlt' => Yii::t('base', 'Profile picture of {displayName}', ['displayName' => $user->displayName]),
            'online' => $this->serializeOnlineStatus($user),
        ];
    }

    /**
     * `null` when the viewer is looking at themself or the online-status feature is
     * disabled (hidden by admin/user setting), a real status otherwise.
     */
    private function serializeOnlineStatus(User $user): ?bool
    {
        if ($user->id === Yii::$app->user->id) {
            return null;
        }

        $isOnlineService = new IsOnlineService($user);

        return $isOnlineService->isEnabled() ? $isOnlineService->getStatus() : null;
    }
}
