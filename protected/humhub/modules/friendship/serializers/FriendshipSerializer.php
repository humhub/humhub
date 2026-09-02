<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\serializers;

use humhub\modules\friendship\models\Friendship;
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
