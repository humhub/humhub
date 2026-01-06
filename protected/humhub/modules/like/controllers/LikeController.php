<?php

namespace humhub\modules\like\controllers;

use humhub\components\behaviors\AccessControl;
use humhub\components\Controller;
use humhub\models\RecordMap;
use humhub\modules\content\interfaces\ContentProvider;
use humhub\modules\like\services\LikeService;
use humhub\modules\user\widgets\UserListBox;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class LikeController extends Controller
{
    private LikeService $likeService;

    public function beforeAction($action): bool
    {
        $recordId = (int)Yii::$app->request->get('recordId');
        $target = RecordMap::getById($recordId, ContentProvider::class);

        if (!$target) {
            throw new NotFoundHttpException();
        }

        if (!$target->content->canView()) {
            throw new ForbiddenHttpException();
        }

        $this->likeService = new LikeService($target);
        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::class,
            ],
        ];
    }

    public function actionLike()
    {
        $this->forcePostRequest();

        if (!$this->likeService->canLike()) {
            throw new ForbiddenHttpException();
        }

        $this->likeService->like();

        return $this->asJson([
            'currentUserLiked' => $this->likeService->hasLiked(),
            'likeCounter' => $this->likeService->getCount(),
        ]);
    }

    public function actionUnlike()
    {
        $this->forcePostRequest();
        $this->likeService->unlike();

        return $this->asJson([
            'currentUserLiked' => $this->likeService->hasLiked(),
            'likeCounter' => $this->likeService->getCount()
        ]);
    }

    public function actionUserList()
    {
        $title = Yii::t('LikeModule.base', "<strong>Users</strong> who like this");
        return $this->renderAjaxContent(
            UserListBox::widget(['query' => $this->likeService->getUserQuery(), 'title' => $title])
        );
    }
}
