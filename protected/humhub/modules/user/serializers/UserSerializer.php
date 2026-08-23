<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\serializers;

use humhub\modules\user\models\User;
use yii\helpers\Url;

/**
 * Serializes a {@see User} for the HTTP API (see `docs/develop/concept-api.md`).
 *
 * This is the short representation every other API shape embeds when it needs to describe a
 * user (a comment's author, a liking user, a space owner). Conventions of the current API
 * version: camelCase field names, ISO-8601 timestamps.
 *
 * The shape is caller-neutral - identical for every caller allowed to see it - which is what
 * lets the payloads embedding it be cached. Two things a user representation might be
 * expected to carry are therefore deliberately absent:
 *
 * - **the accessible name of the profile image**, because it is localized presentation text a
 *   client builds itself (see `user/vue/UserImage.vue`),
 * - **the online status**, because presence is volatile and depends on who is asking (nobody
 *   sees an indicator on their own records). A client resolves it separately.
 *
 * @since 1.19
 */
class UserSerializer
{
    /**
     * @return array{
     *     id: int,
     *     guid: string,
     *     displayName: string,
     *     url: string,
     *     imageUrl: string,
     *     contentContainerId: int|null,
     * }
     */
    public static function short(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        return [
            'id' => $user->id,
            'guid' => $user->guid,
            'displayName' => $user->displayName,
            'url' => $user->getUrl(true),
            // Absolute, like every other URL the API emits — a client may not share the
            // platform's origin.
            'imageUrl' => Url::to($user->getProfileImage()->getUrl(), true),
            'contentContainerId' => $user->contentcontainer_id,
        ];
    }
}
