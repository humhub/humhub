<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\serializers;

use humhub\modules\content\models\ContentContainerSetting;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\space\Module;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\User;
use Yii;
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
 * `space\controllers\api\SpaceController::actionStates()`).
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
     * The `visibility` values of the API, by the stored constant.
     */
    public const VISIBILITIES = [
        Space::VISIBILITY_NONE => 'private',
        Space::VISIBILITY_REGISTERED_ONLY => 'registered',
        Space::VISIBILITY_ALL => 'public',
    ];

    /**
     * The list representation: the short one plus what a list of spaces is browsed and
     * filtered by.
     *
     * `bannerUrl` is `null` without a banner of the space's own (like `imageUrl`).
     * `memberCount` and `followerCount` count active users that are not hidden — the same
     * number for every caller; each is `null` where the space does not show it (members:
     * "Hide Members", followers: "Hide Followers" or following disabled), as its header does.
     *
     * Counting costs queries: without `$counts` this runs them for the one space, a page of
     * spaces goes through {@see self::batch()}, which counts all of them at once.
     *
     * @param array{memberCount: int|null, followerCount: int|null}|null $counts precomputed
     *        by {@see self::batch()}
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
     *     visibility: string|null,
     *     archived: bool,
     *     bannerUrl: string|null,
     *     memberCount: int|null,
     *     followerCount: int|null,
     * }
     */
    public static function list(Space $space, ?array $counts = null): array
    {
        $counts ??= self::counts([$space])[$space->id];
        $banner = $space->getBannerImage();

        return array_merge(self::short($space), [
            'description' => $space->description === '' ? null : $space->description,
            'tags' => $space->getTags(),
            // A named value rather than the stored integer, like every other enum of the API.
            'visibility' => self::VISIBILITIES[(int)$space->visibility] ?? null,
            'archived' => $space->isArchived(),
            'bannerUrl' => $banner->exists() ? $banner->getUrl(null, true) : null,
            'memberCount' => $counts['memberCount'],
            'followerCount' => $counts['followerCount'],
        ]);
    }

    /**
     * Serializes a whole page of spaces: {@see self::list()} for each, with the member and
     * follower counts of the page taken in one grouped query each (plus one for the spaces'
     * "hide" settings) instead of per space.
     *
     * @param Space[] $spaces
     */
    public static function batch(array $spaces): array
    {
        $counts = self::counts($spaces);

        return array_map(static fn(Space $space): array => self::list($space, $counts[$space->id]), $spaces);
    }

    /**
     * The member and follower counts of a batch of spaces, as {@see self::list()} carries them:
     * one grouped query each, plus one for the spaces' "hide" settings. A count is `null` where
     * the space does not show it, so `memberCount !== null` also answers "does it show its
     * members at all" and `followerCount !== null` the same for followers.
     *
     * @param Space[] $spaces
     * @return array<int, array{memberCount: int|null, followerCount: int|null}> by space id
     */
    public static function counts(array $spaces): array
    {
        if ($spaces === []) {
            return [];
        }

        $ids = array_map(static fn(Space $space) => $space->id, $spaces);

        $members = Membership::find()
            ->select(['space_membership.space_id', 'count' => 'COUNT(*)'])
            ->innerJoin('user', 'user.id = space_membership.user_id')
            ->where(['space_membership.space_id' => $ids, 'space_membership.status' => Membership::STATUS_MEMBER])
            ->andWhere(self::countedUser())
            ->groupBy('space_membership.space_id')
            ->asArray()
            ->all();
        $members = array_map('intval', array_column($members, 'count', 'space_id'));

        $followers = Follow::find()
            ->select(['user_follow.object_id', 'count' => 'COUNT(*)'])
            ->innerJoin('user', 'user.id = user_follow.user_id')
            ->where(['user_follow.object_model' => Space::class, 'user_follow.object_id' => $ids])
            ->andWhere(self::countedUser())
            ->groupBy('user_follow.object_id')
            ->asArray()
            ->all();
        $followers = array_map('intval', array_column($followers, 'count', 'object_id'));

        $hidden = self::hiddenCounts($spaces);

        /** @var Module $module */
        $module = Yii::$app->getModule('space');

        $counts = [];
        foreach ($spaces as $space) {
            $counts[$space->id] = [
                'memberCount' => $hidden[$space->id]['hideMembers'] ? null : ($members[$space->id] ?? 0),
                'followerCount' => $module->disableFollow || $hidden[$space->id]['hideFollowers']
                    ? null
                    : ($followers[$space->id] ?? 0),
            ];
        }

        return $counts;
    }

    /**
     * Who counts as a member or follower: an active user that is not hidden.
     */
    private static function countedUser(): array
    {
        return ['and', ['user.status' => User::STATUS_ENABLED], ['!=', 'user.visibility', User::VISIBILITY_HIDDEN]];
    }

    /**
     * The spaces' "Hide Members" / "Hide Followers" settings (see `AdvancedSettings`), read for
     * the whole page at once, falling back to the module's defaults.
     *
     * @param Space[] $spaces
     * @return array<int, array{hideMembers: bool, hideFollowers: bool}> by space id
     */
    private static function hiddenCounts(array $spaces): array
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('space');
        $defaults = $module->getDefaultSettings();

        $byContainer = [];
        foreach ($spaces as $space) {
            $byContainer[$space->contentcontainer_id] = $space->id;
        }

        $rows = ContentContainerSetting::find()
            ->select(['contentcontainer_id', 'name', 'value'])
            ->where([
                'module_id' => 'space',
                'contentcontainer_id' => array_keys($byContainer),
                'name' => ['hideMembers', 'hideFollowers'],
            ])
            ->asArray()
            ->all();

        $hidden = [];
        foreach ($spaces as $space) {
            $hidden[$space->id] = [
                'hideMembers' => (bool)$defaults->defaultHideMembers,
                'hideFollowers' => (bool)$defaults->defaultHideFollowers,
            ];
        }
        foreach ($rows as $row) {
            $hidden[$byContainer[$row['contentcontainer_id']]][$row['name']] = (bool)$row['value'];
        }

        return $hidden;
    }
}
