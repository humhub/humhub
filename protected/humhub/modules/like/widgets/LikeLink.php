<?php

namespace humhub\modules\like\widgets;

use humhub\models\RecordMap;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\like\services\LikeService;
use Yii;
use yii\base\Widget;

class LikeLink extends Widget
{
    public ContentActiveRecord|ContentAddonActiveRecord $object;

    private LikeService $likeService;

    public function beforeRun()
    {
        $this->likeService = new LikeService($this->object);

        $guestHideComments = Yii::$app->getModule('comment')->guestHideComments;
        if (!(Yii::$app->user->isGuest && $guestHideComments) && !$this->likeService->canLike()) {
            return false;
        }

        return parent::beforeRun();
    }

    public function run()
    {
        return $this->render('likeLink', [
            'recordId' => RecordMap::getId($this->object),
            'likeCount' => $this->likeService->getCount(),
            'currentUserLiked' => $this->likeService->hasLiked(),
        ]);
    }
}
