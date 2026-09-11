<?php

namespace humhub\modules\like\widgets;

use humhub\models\RecordMap;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\like\assets\LikeVueAsset;
use humhub\modules\like\services\LikeService;
use humhub\widgets\VueWidget;
use Yii;

class LikeLink extends VueWidget
{
    public ContentActiveRecord|ContentAddonActiveRecord $object;

    protected string $component = 'LikeButton';

    protected ?string $assetBundle = LikeVueAsset::class;

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

    /**
     * @inheritdoc
     */
    protected function getProps(): array
    {
        return [
            'recordId' => RecordMap::getId($this->object),
            'likeCount' => $this->likeService->getCount(),
            'currentUserLiked' => Yii::$app->user->isGuest ? false : $this->likeService->hasLiked(),
        ];
    }
}
