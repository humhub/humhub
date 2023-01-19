<?php

namespace humhub\modules\like\widgets;

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;
use humhub\modules\like\Module;
use humhub\modules\like\models\Like as LikeModel;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\like\permissions\CanLike;

/**
 * This widget is used to show a like link inside the wall entry controls.
 *
 * @package humhub.modules_core.like
 * @since 0.5
 */
class LikeLink extends \yii\base\Widget
{

    /**
     * The Object to be liked
     *
     * @var LikeModel|ContentActiveRecord
     */
    public $object;

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        if (
            !Yii::$app->getModule('like')->isEnabled ||
            (
                isset($this->object->content->container) &&
                $this->object->content->container instanceof ContentContainerActiveRecord &&
                !$this->object->content->container->can(CanLike::class)
            )
        ) {
            return false;
        }

        return parent::beforeRun();
    }

    /**
     * Executes the widget.
     */
    public function run()
    {
        $currentUserLiked = false;
        /** @var Module $module */
        $module = Yii::$app->getModule('like');
        $canLike = $module->canLike($this->object);

        $likes = LikeModel::GetLikes(get_class($this->object), $this->object->id);
        foreach ($likes as $like) {
            if ($like->user->id == Yii::$app->user->id) {
                $currentUserLiked = true;
            }
        }

        return $this->render('likeLink', [
                    'canLike' => $canLike,
                    'object' => $this->object,
                    'likes' => $likes,
                    'currentUserLiked' => $currentUserLiked,
                    'id' => $this->object->getUniqueId(),
                    'likeUrl' => Url::to(['/like/like/like', 'contentModel' => get_class($this->object), 'contentId' => $this->object->id]),
                    'unlikeUrl' => Url::to(['/like/like/unlike', 'contentModel' => get_class($this->object), 'contentId' => $this->object->id]),
                    'userListUrl' => Url::to(['/like/like/user-list', 'contentModel' => get_class($this->object), 'contentId' => $this->object->getPrimaryKey()]),
                    'title' => $this->generateLikeTitleText($currentUserLiked, $likes)
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
                $userlist .= Yii::t('LikeModule.base', 'You'). "\n";
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
                    $userlist .= Html::encode($likes[$i]->user->displayName). "\n";
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

?>
