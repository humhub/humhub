<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\serializers;

use humhub\components\api\SerializeEvent;
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
 * {@see self::list()} is the longer form the space list endpoint answers with. It is
 * deliberately caller-neutral: nothing in it depends on who is asking, so the same
 * representation serves a space chooser, a picker and any other list. What IS caller-specific
 * — whether the caller is a member, how many items they have not seen — is not a field here;
 * it is the business of the endpoint asking for it (see
 * `space\controllers\api\SpaceController::actionNewItemCounts()`).
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

    /**
     * The list representation: the short one plus what a list of spaces is browsed and
     * filtered by.
     *
     * @return array{
     *     id: int,
     *     guid: string,
     *     name: string,
     *     url: string,
     *     color: string|null,
     *     imageUrl: string|null,
     *     contentContainerId: int|null,
     *     description: string|null,
     *     tags: string[],
     *     visibility: int|null,
     *     archived: bool,
     *     extensions: array,
     * }
     *
     * @param array|null $extensionData the `namespace => data` map of this space, as
     *        {@see SerializeEvent::collectFor()} returns it — pass it when the caller already
     *        collected a whole batch, so the event fires once per response rather than per space
     */
    public static function list(Space $space, ?array $extensionData = null): array
    {
        $extensionData ??= SerializeEvent::collectFor(Space::class, [$space])[$space->id] ?? [];

        return array_merge(self::short($space), [
            'description' => $space->description === '' ? null : $space->description,
            'tags' => $space->getTags(),
            'visibility' => $space->visibility === null ? null : (int)$space->visibility,
            'archived' => $space->isArchived(),
            // (object) so "nothing attached" serializes as `{}` rather than `[]`.
            'extensions' => $extensionData === [] ? (object)[] : $extensionData,
        ]);
    }

    /**
     * Serializes a whole page of spaces, firing the extension event once for all of them.
     *
     * @param Space[] $spaces
     */
    public static function batch(array $spaces): array
    {
        $extensionData = SerializeEvent::collectFor(Space::class, $spaces);

        return array_map(
            static fn(Space $space): array => self::list($space, $extensionData[$space->id] ?? []),
            $spaces,
        );
    }
}
