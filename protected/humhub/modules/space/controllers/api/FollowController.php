<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers\api;

use humhub\components\api\BaseController;
use humhub\modules\space\models\Space;
use humhub\modules\space\serializers\FollowSerializer;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * The caller's follow relationship to a space, `space/<id>/follow` (see
 * `docs/develop/concept-api.md`), consumed by the `FollowButton` island.
 *
 * A relationship of the caller towards the space, so it is set with `PUT` and removed with
 * `DELETE`; `GET` reads it. Every verb answers the resulting state
 * ({@see FollowSerializer::state()}: `isFollowing`, `followerCount`, `canFollow`), and both
 * writes are idempotent — following what is followed, or unfollowing what is not, answers the
 * state rather than an error, so a client acting on a stale view ends up with the truth.
 *
 * `403` is what the caller may not do: following as a member (members cannot follow) or while
 * following is disabled, and anything on a space they may not see or are blocked from. Ending
 * a follow stays possible when following was disabled after the fact.
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
     * The caller's follow state of the given space.
     */
    public function actionState($id)
    {
        return FollowSerializer::state($this->findSpace((int)$id));
    }

    /**
     * Follows the space — without notifications, as the space's follow button always did.
     */
    public function actionFollow($id)
    {
        $space = $this->findSpace((int)$id);

        if (!$space->isFollowedByUser()) {
            if (!FollowSerializer::canFollow($space)) {
                throw new ForbiddenHttpException(Yii::t('ContentModule.base', 'This action is disabled!'));
            }

            $space->follow(null, false);
        }

        return FollowSerializer::state($this->findSpace($space->id));
    }

    /**
     * Stops following the space.
     */
    public function actionUnfollow($id)
    {
        $space = $this->findSpace((int)$id);

        if ($space->isFollowedByUser()) {
            $space->unfollow();
        }

        return FollowSerializer::state($this->findSpace($space->id));
    }

    /**
     * @throws NotFoundHttpException for an unknown space
     * @throws ForbiddenHttpException for a space the caller may not see, or one that blocked them
     */
    private function findSpace(int $id): Space
    {
        if (!Space::find()->where(['id' => $id])->exists()) {
            throw new NotFoundHttpException();
        }

        $space = Space::find()->visible()->filterBlockedSpaces()->andWhere(['space.id' => $id])->one();

        if ($space === null) {
            throw new ForbiddenHttpException();
        }

        return $space;
    }
}
