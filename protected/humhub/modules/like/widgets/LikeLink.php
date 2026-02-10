<?php

namespace humhub\modules\like\widgets;

use humhub\models\RecordMap;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\like\services\LikeService;
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
        return $this->render('likeLink', [
            'likeCount' => $this->likeService->getCount(),
            'currentUserLiked' => $this->likeService->hasLiked(),
            'id' => 'like_' . RecordMap::getId($this->object),
            'likeUrl' => Url::to(['/like/like/like', 'recordId' => RecordMap::getId($this->object)]),
            'unlikeUrl' => Url::to(['/like/like/unlike', 'recordId' => RecordMap::getId($this->object)]),
            'userListUrl' => Url::to(
                ['/like/like/user-list', 'recordId' => RecordMap::getId($this->object)],
            ),
            'title' => $this->likeService->generateLikeTitleText(),
        ]);
    }
}
