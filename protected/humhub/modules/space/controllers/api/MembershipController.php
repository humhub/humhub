<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers\api;

use humhub\components\api\BaseController;
use humhub\modules\space\models\forms\RequestMembershipForm;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\space\serializers\MembershipSerializer;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * The caller's own membership in a space (see `docs/develop/concept-api.md`), consumed by the
 * `MembershipButton` island.
 *
 * ## One resource, two verbs
 *
 * Every transition the membership button offers is one of two things, so the endpoint has two
 * writing verbs instead of one action per button:
 *
 * - `POST` **affirms** membership: joining a free space, applying to a space that approves
 *   (with an optional `message`), or accepting an invite. Which of the three it is follows from
 *   the current state and the space's join policy — the server decides, the client does not
 *   have to reimplement that rule.
 * - `DELETE` **removes** it: leaving, withdrawing an application, declining an invite.
 *
 * Both answer the new state, in the same shape `GET` returns, so a client renders from one
 * representation and never has to derive what happened.
 *
 * ## Why this replaces a re-render
 *
 * The server-rendered membership button used to be re-rendered after every transition, which
 * meant its presentation options travelled to the client and back (hardened in #8381). With the
 * state as data, nothing but the state crosses the wire — see the widget's own docblock.
 *
 * @since 1.20
 */
class MembershipController extends BaseController
{
    /**
     * @inheritdoc
     */
    protected bool $enableSessionAuth = true;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'state' => ['GET', 'HEAD'],
                    'affirm' => ['POST'],
                    'remove' => ['DELETE'],
                ],
            ],
        ]);
    }

    /**
     * The caller's membership state in the given space.
     */
    public function actionState($id)
    {
        return MembershipSerializer::state($this->findSpace((int)$id));
    }

    /**
     * Joins the space, applies for membership, or accepts a pending invite — whichever the
     * current state and the space's join policy call for.
     *
     * `message` is the application text of a space that approves memberships — required there
     * ({@see RequestMembershipForm}, the same model the web form validates against), ignored
     * where no approval takes place.
     */
    public function actionAffirm($id)
    {
        $space = $this->findSpace((int)$id);
        $membership = $space->getMembership();

        if ($membership !== null && (int)$membership->status === Membership::STATUS_INVITED) {
            $space->addMember(Yii::$app->user->id);

            return MembershipSerializer::state($space);
        }

        if ($membership !== null) {
            // Already a member or already applied - nothing to affirm, and the state says so.
            throw new ForbiddenHttpException(
                Yii::t('SpaceModule.base', 'Could not request membership!'),
            );
        }

        if (!$space->canJoin()) {
            throw new ForbiddenHttpException(
                Yii::t('SpaceModule.base', 'You are not allowed to join this space!'),
            );
        }

        if ((int)$space->join_policy === Space::JOIN_POLICY_APPLICATION) {
            $form = new RequestMembershipForm([
                'message' => (string)Yii::$app->request->getBodyParam('message', ''),
            ]);

            if (!$form->validate()) {
                return $this->validationErrors($form);
            }

            $space->requestMembership(Yii::$app->user->id, $form->message);
        } else {
            $space->addMember(Yii::$app->user->id);
        }

        return MembershipSerializer::state($space);
    }

    /**
     * Removes the caller's membership: leaving the space, withdrawing an application or
     * declining an invite. Same guards as the web route
     * (`space\controllers\MembershipController::actionRevokeMembership()`).
     */
    public function actionRemove($id)
    {
        $space = $this->findSpace((int)$id);

        if ($space->getMembership() === null) {
            // Nothing to remove; answering the state rather than an error keeps the client's
            // view correct when it acts on a stale one.
            return MembershipSerializer::state($space);
        }

        if ($space->isSpaceOwner()) {
            throw new ForbiddenHttpException(
                Yii::t('SpaceModule.base', 'As owner you cannot revoke your membership!'),
            );
        }

        if (!$space->canLeave()) {
            throw new ForbiddenHttpException(
                Yii::t('SpaceModule.base', 'Sorry, you are not allowed to leave this space!'),
            );
        }

        $space->removeMember();

        return MembershipSerializer::state($space);
    }

    /**
     * The web routes reach their space through `ContentContainerController`, whose access layer
     * does this; an API controller has to state it itself.
     *
     * A caller may act on their membership in a space they can see, in one they are a member of
     * (private spaces are invisible but their members act on them), and in one they were
     * invited to or applied for — an invite to a private space has to be acceptable.
     *
     * @throws NotFoundHttpException for an unknown space
     * @throws ForbiddenHttpException for a space the caller may not see, or one that blocked
     *         them (the button is not rendered for those either)
     */
    protected function findSpace(int $id): Space
    {
        $space = Space::findOne(['id' => $id]);

        if ($space === null) {
            throw new NotFoundHttpException();
        }

        $isVisible = (int)$space->visibility !== Space::VISIBILITY_NONE
            || $space->getMembership() !== null
            || $space->canAccessPrivateContent();

        if (!$isVisible || $space->isBlockedForUser()) {
            throw new ForbiddenHttpException();
        }

        return $space;
    }
}
