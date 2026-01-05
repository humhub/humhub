<?php

namespace humhub\modules\like\controllers;

use humhub\components\behaviors\AccessControl;
use humhub\components\Controller;
use humhub\helpers\DataTypeHelper;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\like\services\LikeService;
use Yii;
use humhub\modules\user\widgets\UserListBox;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class LikeController extends Controller
{
    private LikeService $likeService;

    public function beforeAction($action)
    {
        $modelClass = Yii::$app->request->get('contentModel');

        /** @var ContentAddonActiveRecord|ContentActiveRecord $modelClass */
        $modelClass = DataTypeHelper::matchClassType(
            $modelClass,
            [ContentAddonActiveRecord::class, ContentActiveRecord::class],
            true,
        );

        $target = $modelClass::findOne(['id' => (int)Yii::$app->request->get('contentId')]);

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
