<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\serializers;

use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use Yii;

/**
 * The caller's membership state in a space, as the membership API answers it and the
 * `MembershipButton` island renders it (see `docs/develop/concept-api.md`).
 *
 * Unlike most shapes of this API this one is **caller context by definition** — it is the
 * answer to "what is my relationship to this space", so it is never cached and never embedded
 * in a space payload.
 *
 * It carries the state AND what the caller may do next, because the two are not derivable from
 * one another: whether a non-member may join depends on the space's join policy, its status and
 * the caller's own permissions, and whether a member may leave depends on ownership and the
 * space configuration. A client deciding that itself would reimplement authorization.
 *
 * @since 1.20
 */
class MembershipSerializer
{
    /**
     * @var string no membership and no pending request
     */
    public const STATE_NONE = 'none';

    /**
     * @var string invited by someone, not accepted yet
     */
    public const STATE_INVITED = 'invited';

    /**
     * @var string applied for membership, waiting for approval
     */
    public const STATE_APPLICANT = 'applicant';

    /**
     * @var string member of the space
     */
    public const STATE_MEMBER = 'member';

    /**
     * @return array{
     *     state: string,
     *     canJoin: bool,
     *     needsApproval: bool,
     *     canLeave: bool,
     *     isOwner: bool,
     *     isFollowing: bool,
     * }
     */
    public static function state(Space $space): array
    {
        $membership = $space->getMembership();
        $isOwner = $space->isSpaceOwner();

        return [
            'state' => self::resolveState($membership),
            // Whether joining (or applying) is possible at all — the space's join policy, its
            // status and the caller's permissions all feed into this.
            'canJoin' => $space->canJoin(),
            // A join request needs approval instead of taking effect immediately, so the
            // client asks for a message first.
            'needsApproval' => (int)$space->join_policy === Space::JOIN_POLICY_APPLICATION,
            // Leaving covers withdrawing a request and declining an invite as well; an owner
            // can never leave their own space.
            'canLeave' => $membership !== null && !$isOwner && $space->canLeave(),
            'isOwner' => $isOwner,
            // Not membership state, but the state of the sibling follow button the same UI
            // shows: following is only offered to non-members, so a membership change flips it.
            'isFollowing' => !Yii::$app->user->isGuest && $space->isFollowedByUser(),
        ];
    }

    private static function resolveState(?Membership $membership): string
    {
        if ($membership === null) {
            return self::STATE_NONE;
        }

        return match ((int)$membership->status) {
            Membership::STATUS_INVITED => self::STATE_INVITED,
            Membership::STATUS_APPLICANT => self::STATE_APPLICANT,
            Membership::STATUS_MEMBER => self::STATE_MEMBER,
            default => self::STATE_NONE,
        };
    }
}
