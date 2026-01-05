<?php

namespace humhub\modules\like\widgets;

use humhub\components\behaviors\PolymorphicRelation;
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
            'id' => $this->object->getUniqueId(),
            'likeUrl' => Url::to(
                [
                    '/like/like/like',
                    'contentModel' => PolymorphicRelation::getObjectModel($this->object),
                    'contentId' => $this->object->id
                ]
            ),
            'unlikeUrl' => Url::to(
                [
                    '/like/like/unlike',
                    'contentModel' => PolymorphicRelation::getObjectModel($this->object),
                    'contentId' => $this->object->id
                ]
            ),
            'userListUrl' => Url::to(
                [
                    '/like/like/user-list',
                    'contentModel' => PolymorphicRelation::getObjectModel($this->object),
                    'contentId' => $this->object->getPrimaryKey()
                ]
            ),
            'title' => $this->likeService->generateLikeTitleText(),
        ]);
    }
}
