<?php

namespace humhub\modules\like\widgets;

use humhub\models\RecordMap;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\like\services\LikeService;
use humhub\modules\like\models\Like as LikeModel;
use humhub\modules\like\Module;
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
        $currentUserLiked = false;
        /** @var Module $module */
        $module = Yii::$app->getModule('like');
        $canLike = $module->canLike($this->object);

        $likes = LikeModel::GetLikes(PolymorphicRelation::getObjectModel($this->object), $this->object->id);
        foreach ($likes as $like) {
            if ($like->user->id == Yii::$app->user->id) {
                $currentUserLiked = true;
            }
        }

        return LikeWidget::widget([
            'props' => [
                'isGuest' => Yii::$app->user->isGuest,
                'canLike' => $canLike,
                'currentUserLiked' => $currentUserLiked,
                'likeCount' => count($likes),
                'title' => $this->generateLikeTitleText($currentUserLiked, $likes),
                'urls' => [
                    'loginUrl' => Url::to(Yii::$app->user->loginUrl),
                    'likeUrl' => Url::to(['/like/like/like', 'contentModel' => PolymorphicRelation::getObjectModel($this->object), 'contentId' => $this->object->id]),
                    'unlikeUrl' => Url::to(['/like/like/unlike', 'contentModel' => PolymorphicRelation::getObjectModel($this->object), 'contentId' => $this->object->id]),
                    'translationsUrl' => Url::to(['/translation/index', 'category' => 'LikeModule.base']),
                ]
            ]
        ]);

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

    private function generateLikeTitleText($currentUserLiked, $likes)
    {
        $userlist = ""; // variable for users output
        $maxUser = 5; // limit for rendered users inside the tooltip
        $previewUserCount = 0;

        // if the current user also likes
        if ($currentUserLiked == true) {
            // if only one user likes
            if (count($likes) == 1) {
                // output, if the current user is the only one
                return Yii::t('LikeModule.base', 'You like this.');
            } else {
                // output, if more users like this
                $userlist .= Yii::t('LikeModule.base', 'You') . "\n";
                $previewUserCount++;
            }
        }

        for ($i = 0, $likesCount = count($likes); $i < $likesCount; $i++) {

            // if only one user likes
            if ($likesCount == 1) {
                // check, if you liked
                if ($likes[$i]->user->guid != Yii::$app->user->guid) {
                    // output, if an other user liked
                    return Html::encode($likes[$i]->user->displayName) . Yii::t('LikeModule.base', ' likes this.');
                }
            } else {
                // check, if you liked
                if ($likes[$i]->user->guid != Yii::$app->user->guid) {
                    // output, if an other user liked
                    $userlist .= Html::encode($likes[$i]->user->displayName) . "\n";
                    $previewUserCount++;
                }

                // check if exists more user as limited
                if ($i == $maxUser) {
                    if ((int)(count($likes) - $previewUserCount) !== 0) {
                        // output with the number of not rendered users
                        $userlist .= Yii::t('LikeModule.base', 'and {count} more like this.', ['{count}' => (int)(count($likes) - $previewUserCount)]);
                    }

                    // stop the loop
                    break;
                }
            }
        }

        return $userlist;
    }

}
