<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\controllers\api;

use humhub\components\api\BaseController;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\friendship\Module;
use humhub\modules\friendship\serializers\FriendshipSerializer;
use humhub\modules\user\models\User;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

/**
 * The caller's friendship with a user (see `docs/develop/concept-api.md`), consumed by the
 * `FriendshipButton` island.
 *
 * ## One resource, two verbs
 *
 * The same shape the space membership endpoint has, for the same reason: every transition the
 * button offers is either affirming or removing a relationship, so there are two writing verbs
 * instead of one action per button.
 *
 * - `POST` **affirms** it: sending a request, or accepting the one this user sent. Which of the
 *   two it is follows from the current state — the server decides.
 * - `DELETE` **removes** it: withdrawing a sent request, denying a received one, or ending the
 *   friendship.
 *
 * Both answer the new state, in the same shape `GET` returns.
 *
 * ## Why this replaces a re-render
 *
 * The server-rendered friendship button was re-rendered after every transition, which meant
 * its presentation options travelled to the client and back (hardened in #8381). With the state
 * as data, nothing but the state crosses the wire — see the widget's own docblock.
 *
 * @property Module $module
 * @since 1.20
 */
class FriendshipController extends BaseController
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
     * @inheritdoc
     * @throws HttpException the friendship system can be switched off entirely, in which case
     *         these endpoints do not exist — same answer the web controller gives.
     */
    public function beforeAction($action)
    {
        if (!$this->module->isFriendshipEnabled()) {
            throw new NotFoundHttpException(
                Yii::t('FriendshipModule.base', 'Friendship system is not enabled!'),
            );
        }

        return parent::beforeAction($action);
    }

    /**
     * The caller's friendship state with the given user.
     */
    public function actionState($id)
    {
        return FriendshipSerializer::state($this->findUser((int)$id));
    }

    /**
     * Sends a friendship request, or accepts the one this user sent — whichever the current
     * state calls for.
     */
    public function actionAffirm($id)
    {
        $user = $this->findUser((int)$id);
        $state = FriendshipSerializer::state($user)['state'];

        if ($state !== FriendshipSerializer::STATE_NONE && $state !== FriendshipSerializer::STATE_REQUEST_RECEIVED) {
            // Already friends, or already waiting for an answer - nothing to affirm, and the
            // state says so.
            throw new ForbiddenHttpException();
        }

        // One call for both: a request where there is none, the accepting counterpart where
        // this user already sent one (see Friendship::afterSave()).
        Friendship::add(Yii::$app->user->getIdentity(), $user);

        return FriendshipSerializer::state($user);
    }

    /**
     * Removes the friendship: withdrawing a sent request, denying a received one, or ending an
     * existing friendship.
     */
    public function actionRemove($id)
    {
        $user = $this->findUser((int)$id);

        if (FriendshipSerializer::state($user)['state'] === FriendshipSerializer::STATE_NONE) {
            // Nothing to remove; answering the state rather than an error keeps the client's
            // view correct when it acts on a stale one.
            return FriendshipSerializer::state($user);
        }

        Friendship::cancel(Yii::$app->user->getIdentity(), $user);

        return FriendshipSerializer::state($user);
    }

    /**
     * A friendship is between the caller and somebody else — there is no friendship resource
     * for the caller themselves, and the button is not rendered there either
     * ({@see \humhub\modules\friendship\widgets\FriendshipButton::isVisibleForUser()}).
     *
     * @throws NotFoundHttpException for an unknown user
     * @throws ForbiddenHttpException for the caller's own user
     */
    protected function findUser(int $id): User
    {
        $user = User::findOne(['id' => $id]);

        if ($user === null) {
            throw new NotFoundHttpException();
        }

        if ($user->isCurrentUser()) {
            throw new ForbiddenHttpException();
        }

        return $user;
    }
}
