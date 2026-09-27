<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers\api;

use humhub\components\api\BaseController;
use humhub\modules\user\models\User;
use humhub\modules\user\serializers\FollowSerializer;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * The caller's follow relationship to a user, `user/<id>/follow` (see
 * `docs/develop/concept-api.md`) — the user counterpart of the space's
 * {@see \humhub\modules\space\controllers\api\FollowController}.
 *
 * A relationship of the caller towards the user, so it is set with `PUT` and removed with
 * `DELETE`; `GET` reads it. Every verb answers the resulting state
 * ({@see FollowSerializer::state()}: `isFollowing`, `followerCount`, `canFollow`), and both
 * writes are idempotent — following who is followed, or unfollowing who is not, answers the
 * state rather than an error, so a client acting on a stale view ends up with the truth.
 *
 * `403` is what the caller may not do: anything on themselves (there is no follow
 * relationship to oneself) or on a user they may not see or who blocked them, and following
 * while following is disabled. Ending a follow stays possible when following was disabled
 * after the fact.
 *
 * @since 1.20
 */
class FollowController extends BaseController
{
    /**
     * @inheritdoc
     */
    protected bool $allowSessionAuth = true;

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
                    'follow' => ['PUT'],
                    'unfollow' => ['DELETE'],
                ],
            ],
        ]);
    }

    /**
     * The caller's follow state of the given user.
     */
    public function actionState($id)
    {
        return FollowSerializer::state($this->findUser((int)$id));
    }

    /**
     * Follows the user — with notifications, as the profile's follow button always did.
     */
    public function actionFollow($id)
    {
        $user = $this->findUser((int)$id);

        if (!$user->isFollowedByUser()) {
            if (!FollowSerializer::canFollow($user)) {
                throw new ForbiddenHttpException(Yii::t('ContentModule.base', 'This action is disabled!'));
            }

            $user->follow(Yii::$app->user->getIdentity());
        }

        return FollowSerializer::state($this->findUser($user->id));
    }

    /**
     * Stops following the user.
     */
    public function actionUnfollow($id)
    {
        $user = $this->findUser((int)$id);

        if ($user->isFollowedByUser()) {
            $user->unfollow();
        }

        return FollowSerializer::state($this->findUser($user->id));
    }

    /**
     * @throws NotFoundHttpException for an unknown user
     * @throws ForbiddenHttpException for the caller themselves, a user the caller may not see,
     *         or one who blocked them
     */
    private function findUser(int $id): User
    {
        if (!User::find()->where(['id' => $id])->exists()) {
            throw new NotFoundHttpException();
        }

        $user = User::find()->available()->andWhere(['user.id' => $id])->one();

        if ($user === null || $user->isCurrentUser()) {
            throw new ForbiddenHttpException();
        }

        return $user;
    }
}
