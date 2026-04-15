<?php

namespace humhub\modules\like\widgets;

use humhub\models\RecordMap;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\like\services\LikeService;
use humhub\modules\like\vue\LikeWidget;
use Yii;
use yii\base\Widget;
use yii\helpers\Url;

class LikeLink extends Widget
{
    public ContentActiveRecord|ContentAddonActiveRecord $object;

    private LikeService $likeService;

    public function beforeRun()
    {
        $this->likeService = new LikeService($this->object);

        if (!$this->likeService->canLike()) {
            return false;
        }

        return parent::beforeRun();
    }

    public function run()
    {
        $recordId = RecordMap::getId($this->object);

        return LikeWidget::widget([
            'props' => [
                'isGuest' => Yii::$app->user->isGuest,
                'canLike' => $this->likeService->canLike(),
                'currentUserLiked' => $this->likeService->hasLiked(),
                'likeCount' => $this->likeService->getCount(),
                'title' => $this->likeService->generateLikeTitleText(),
                'urls' => [
                    'loginUrl' => Url::to(Yii::$app->user->loginUrl),
                    'likeUrl' => Url::to(['/like/like/like', 'recordId' => $recordId]),
                    'unlikeUrl' => Url::to(['/like/like/unlike', 'recordId' => $recordId]),
                    'userListUrl' => Url::to(['/like/like/user-list', 'recordId' => $recordId]),
                ],
            ]
        ]);
    }
}
