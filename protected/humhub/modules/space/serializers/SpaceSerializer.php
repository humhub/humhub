<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\serializers;

use humhub\modules\space\models\Space;
use yii\helpers\Url;

/**
 * Serializes a {@see Space} for the HTTP API (see `docs/develop/concept-api.md`).
 *
 * The counterpart of {@see \humhub\modules\user\serializers\UserSerializer} — the short
 * representation another shape embeds when it needs to name the space something happened in
 * (a notification's space, say). Conventions of the current API version: camelCase field
 * names, absolute URLs.
 *
 * `imageUrl` is `null` when the space has no profile image of its own, because that is what
 * decides whether a client renders the image or the coloured acronym tile
 * (`space\widgets\Image` makes the same distinction server-side, and `space/vue/SpaceImage.vue`
 * reproduces it) — an always-present default image URL would take that choice away.
 *
 * @since 1.20
 */
class SpaceSerializer
{
    /**
     * @return array{
     *     id: int,
     *     guid: string,
     *     name: string,
     *     url: string,
     *     color: string|null,
     *     imageUrl: string|null,
     *     contentContainerId: int|null,
     * }|null
     */
    public static function short(?Space $space): ?array
    {
        if ($space === null) {
            return null;
        }

        return [
            'id' => $space->id,
            'guid' => $space->guid,
            'name' => $space->name,
            'url' => $space->getUrl(true),
            'color' => $space->color,
            'imageUrl' => $space->image->exists() ? Url::to($space->getProfileImage()->getUrl(), true) : null,
            'contentContainerId' => $space->contentcontainer_id,
        ];
    }
}
