<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\serializers;

use humhub\modules\friendship\models\Friendship;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\User;
use Yii;

/**
 * The caller's friendship with a user, as the friendship API answers it and the
 * `FriendshipButton` island renders it (see `docs/develop/concept-api.md`).
 *
 * Like the space membership shape this is **caller context by definition** — it is the answer
 * to "what is my relationship to this user" — so it is never cached and never embedded in a
 * user payload. Unlike membership it needs no "what may I do next" fields: a friendship has no
 * policy to consult, every state offers exactly one affirming and one removing transition, and
 * whether the button exists at all is decided where it is rendered
 * ({@see \humhub\modules\friendship\widgets\FriendshipButton::isVisibleForUser()}).
 *
 * @since 1.20
 */
class FriendshipSerializer
{
    /**
     * @var string no friendship and no pending request
     */
    public const STATE_NONE = 'none';

    /**
     * @var string the caller asked this user, waiting for their answer
     */
    public const STATE_REQUEST_SENT = 'requestSent';

    /**
     * @var string this user asked the caller, waiting for the caller's answer
     */
    public const STATE_REQUEST_RECEIVED = 'requestReceived';

    /**
     * @var string mutual friendship
     */
    public const STATE_FRIENDS = 'friends';

    /**
     * @return array{state: string, isFollowing: bool}
     */
    public static function state(User $user): array
    {
        return [
            'state' => self::resolveState($user),
            // Not friendship state, but the state of the sibling follow button the same UI
            // shows: accepting a friendship follows the other user, so a change flips it.
            'isFollowing' => $user->isFollowedByUser(),
        ];
    }

    /**
     * {@see self::state()} for a batch of users at once — one query for the friendship rows
     * between the caller and all of them, one for the caller's follows — as the user states of
     * a list page need it.
     *
     * @param User[] $users
     * @param int[]|null $followedIds the ids among `$users` the caller follows, when the caller
     *        loaded them already (the follow query is then skipped)
     * @return array<int, array{state: string, isFollowing: bool}> by user id
     */
    public static function states(array $users, ?array $followedIds = null): array
    {
        if ($users === []) {
            return [];
        }

        $ids = array_map(static fn(User $user) => $user->id, $users);
        $me = Yii::$app->user->id;

        $sent = [];
        $received = [];
        $rows = Friendship::find()
            ->select(['user_id', 'friend_user_id'])
            ->where(['or', ['user_id' => $me, 'friend_user_id' => $ids], ['user_id' => $ids, 'friend_user_id' => $me]])
            ->asArray()
            ->all();
        foreach ($rows as $row) {
            if ((int)$row['user_id'] === (int)$me) {
                $sent[(int)$row['friend_user_id']] = true;
            } else {
                $received[(int)$row['user_id']] = true;
            }
        }

        $following = array_flip(array_map('intval', $followedIds ?? Follow::find()
            ->select('object_id')
            ->where(['user_id' => $me, 'object_model' => User::class, 'object_id' => $ids])
            ->column()));

        $states = [];
        foreach ($ids as $id) {
            // The same precedence as Friendship::getStateForUser().
            $states[$id] = [
                'state' => match (true) {
                    isset($sent[$id]) && isset($received[$id]) => self::STATE_FRIENDS,
                    isset($sent[$id]) => self::STATE_REQUEST_SENT,
                    isset($received[$id]) => self::STATE_REQUEST_RECEIVED,
                    default => self::STATE_NONE,
                },
                'isFollowing' => isset($following[$id]),
            ];
        }

        return $states;
    }

    private static function resolveState(User $user): string
    {
        $state = Friendship::getStateForUser(Yii::$app->user->getIdentity(), $user);

        return match ($state) {
            Friendship::STATE_FRIENDS => self::STATE_FRIENDS,
            Friendship::STATE_REQUEST_SENT => self::STATE_REQUEST_SENT,
            Friendship::STATE_REQUEST_RECEIVED => self::STATE_REQUEST_RECEIVED,
            default => self::STATE_NONE,
        };
    }
}
