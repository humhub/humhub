<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\serializers;

use humhub\modules\admin\models\forms\PeopleSettingsForm;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Yii;
use yii\helpers\StringHelper;
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
 *   sees an indicator on their own records). A client reads it from `user/states` (`isOnline`).
 *
 * {@see self::list()} is the longer form the user list endpoint answers with. It is caller-neutral
 * for the same reason: whether the caller follows or is a friend of a listed user is not a field
 * here, it is what `user\controllers\api\UserController::actionStates()` answers.
 *
 * @since 1.20
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

    /**
     * The most characters of one {@see self::list()} detail line, as the People card always had.
     */
    public const DETAIL_LENGTH = 200;

    /**
     * The list representation: the short one plus what a list of users is browsed by — the
     * People directory's card.
     *
     * - `bannerUrl` is `null` without a banner of the user's own (like a space's).
     * - `title` is the line under the name the platform shows everywhere (the profile's
     *   `title` unless an administrator configured another, see {@see User::getDisplayNameSub()}),
     *   `null` when empty.
     * - `details` are the up to three profile fields an administrator picked for the People
     *   card (`people.detail1..3`, see {@see PeopleSettingsForm}) as plain text, empty ones left
     *   out — the same for every caller.
     * - `followerCount` counts active users that are not hidden, `null` while following is
     *   disabled; `friendCount` counts mutual friendships the same way, `null` while the
     *   friendship system is off.
     *
     * Counting and resolving the profile fields costs queries: without `$counts` and `$fields`
     * this runs them for the one user, a page of users goes through {@see self::batch()},
     * which does it once for all of them.
     *
     * @param array{followerCount: int|null, friendCount: int|null}|null $counts precomputed
     *        by {@see self::batch()}
     * @param array{title: ProfileField|null, details: ProfileField[]}|null $fields precomputed
     *        by {@see self::cardFields()}
     * @return array{
     *     id: int,
     *     guid: string,
     *     displayName: string,
     *     url: string,
     *     imageUrl: string,
     *     contentContainerId: int|null,
     *     bannerUrl: string|null,
     *     title: string|null,
     *     details: string[],
     *     tags: string[],
     *     followerCount: int|null,
     *     friendCount: int|null,
     * }
     */
    public static function list(User $user, ?array $counts = null, ?array $fields = null): array
    {
        $counts ??= self::counts([$user])[$user->id];
        $fields ??= self::cardFields();
        $banner = $user->getBannerImage();

        /** @var Module $module */
        $module = Yii::$app->getModule('user');
        // User::getDisplayNameSub(), with the field resolved once per page instead of per user.
        $title = trim($module->displayNameSubCallback !== null
            ? $user->displayNameSub
            : self::plainValue($fields['title'], $user));

        $details = [];
        foreach ($fields['details'] as $field) {
            $value = self::plainValue($field, $user);
            if ($value !== '') {
                $details[] = StringHelper::truncate($value, self::DETAIL_LENGTH, '...');
            }
        }

        return array_merge(self::short($user), [
            'bannerUrl' => $banner->exists() ? $banner->getUrl(null, true) : null,
            'title' => $title === '' ? null : $title,
            'details' => $details,
            'tags' => array_values(array_filter(array_map('trim', $user->getTags()), static fn(string $tag) => $tag !== '')),
            'followerCount' => $counts['followerCount'],
            'friendCount' => $counts['friendCount'],
        ]);
    }

    /**
     * The value of a profile field as the card shows it, plain text: the rendered value (option
     * labels, formatted dates) without the markup the rendering may add (the `mailto:` link of
     * an e-mail, the link of a URL).
     */
    private static function plainValue(?ProfileField $field, User $user): string
    {
        // Nothing to render - and a linking field would render a link to the current URL.
        if ($field === null || trim((string)$field->getUserValue($user)) === '') {
            return '';
        }

        // Encoded, so that what is markup and what is text is told apart by strip_tags().
        $rendered = (string)$field->getUserValue($user, false);

        return trim(html_entity_decode(strip_tags($rendered), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * Serializes a whole page of users: {@see self::list()} for each, with the follower and
     * friend counts of the page taken in one grouped query each and the card's profile fields
     * resolved once, instead of per user.
     *
     * @param User[] $users
     */
    public static function batch(array $users): array
    {
        $counts = self::counts($users);
        $fields = self::cardFields();

        return array_map(static fn(User $user): array => self::list($user, $counts[$user->id], $fields), $users);
    }

    /**
     * The profile fields {@see self::list()} renders, in one query: the subtitle's
     * (`displayNameSubFormat`, see {@see User::getDisplayNameSub()}) and those of the People
     * card's detail lines (`people.detail1..3`) in that order — unset ones, and detail fields
     * that are not visible, left out.
     *
     * @return array{title: ProfileField|null, details: ProfileField[]}
     */
    public static function cardFields(): array
    {
        $settings = new PeopleSettingsForm();
        $title = (string)Yii::$app->settings->get('displayNameSubFormat', '');
        $names = array_values(array_filter([$settings->detail1, $settings->detail2, $settings->detail3], static fn($name) => (string)$name !== ''));

        $fields = $names === [] && $title === ''
            ? []
            : ProfileField::find()->where(['internal_name' => array_merge($names, [$title])])->indexBy('internal_name')->all();

        return [
            'title' => $fields[$title] ?? null,
            'details' => array_values(array_filter(array_map(
                static fn(string $name) => isset($fields[$name]) && $fields[$name]->visible ? $fields[$name] : null,
                $names,
            ))),
        ];
    }

    /**
     * The follower and friend counts of a batch of users, as {@see self::list()} carries them:
     * one grouped query each, none for a feature that is off (its count is `null` then).
     *
     * @param User[] $users
     * @return array<int, array{followerCount: int|null, friendCount: int|null}> by user id
     */
    public static function counts(array $users): array
    {
        if ($users === []) {
            return [];
        }

        $ids = array_map(static fn(User $user) => $user->id, $users);

        /** @var Module $module */
        $module = Yii::$app->getModule('user');
        $followEnabled = !$module->disableFollow;
        $friendshipEnabled = (bool)Yii::$app->getModule('friendship')->settings->get('enable');

        $followers = [];
        if ($followEnabled) {
            $rows = Follow::find()
                ->select(['user_follow.object_id', 'count' => 'COUNT(*)'])
                ->innerJoin('user', 'user.id = user_follow.user_id')
                ->where(['user_follow.object_model' => User::class, 'user_follow.object_id' => $ids])
                ->andWhere(self::countedUser())
                ->groupBy('user_follow.object_id')
                ->asArray()
                ->all();
            $followers = array_map('intval', array_column($rows, 'count', 'object_id'));
        }

        $friends = [];
        if ($friendshipEnabled) {
            // A friendship is a request in each direction: count the requests of the listed
            // users that were answered with one back.
            $rows = Friendship::find()
                ->alias('sent')
                ->select(['sent.user_id', 'count' => 'COUNT(*)'])
                ->innerJoin(Friendship::tableName() . ' answered', 'answered.user_id = sent.friend_user_id AND answered.friend_user_id = sent.user_id')
                ->innerJoin('user', 'user.id = sent.friend_user_id')
                ->where(['sent.user_id' => $ids])
                ->andWhere(self::countedUser())
                ->groupBy('sent.user_id')
                ->asArray()
                ->all();
            $friends = array_map('intval', array_column($rows, 'count', 'user_id'));
        }

        $counts = [];
        foreach ($ids as $id) {
            $counts[$id] = [
                'followerCount' => $followEnabled ? ($followers[$id] ?? 0) : null,
                'friendCount' => $friendshipEnabled ? ($friends[$id] ?? 0) : null,
            ];
        }

        return $counts;
    }

    /**
     * Who counts as a follower or friend: an active user that is not hidden.
     */
    private static function countedUser(): array
    {
        return ['and', ['user.status' => User::STATUS_ENABLED], ['!=', 'user.visibility', User::VISIBILITY_HIDDEN]];
    }
}
