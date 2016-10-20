<?php

namespace humhub\modules\like\widgets;

use Yii;
use humhub\modules\like\models\Like;
use yii\helpers\Url;
use yii\helpers\Html;

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
     * @var type
     */
    public $object;

    /**
     * Executes the widget.
     */
    public function run()
    {
        $currentUserLiked = false;

        $likes = Like::GetLikes($this->object->className(), $this->object->id);
        foreach ($likes as $like) {
            if ($like->user->id == Yii::$app->user->id) {
                $currentUserLiked = true;
            }
        }

        return $this->render('likeLink', array(
                    'object' => $this->object,
                    'likes' => $likes,
                    'currentUserLiked' => $currentUserLiked,
                    'id' => $this->object->getUniqueId(),
                    'likeUrl' => Url::to(['/like/like/like', 'contentModel' => $this->object->className(), 'contentId' => $this->object->id]),
                    'unlikeUrl' => Url::to(['/like/like/unlike', 'contentModel' => $this->object->className(), 'contentId' => $this->object->id]),
                    'userListUrl' => Url::to(['/like/like/user-list', 'contentModel' => $this->object->className(), 'contentId' => $this->object->getPrimaryKey()]),
                    'title' => $this->generateLikeTitleText($currentUserLiked, $likes)
        ));
    }

    private function generateLikeTitleText($currentUserLiked, $likes)
    {
        $userlist = ""; // variable for users output
        $maxUser = 5; // limit for rendered users inside the tooltip
        // if the current user also likes
        if ($currentUserLiked == true) {
            // if only one user likes
            if (count($likes) == 1) {
                // output, if the current user is the only one
                $userlist = Yii::t('LikeModule.widgets_views_likeLink', 'You like this.');
            } else {
                // output, if more users like this
                $userlist .= Yii::t('LikeModule.widgets_views_likeLink', 'You'). "\n";
            }
        }

        for ($i = 0, $likesCount = count($likes); $i < $likesCount; $i++) {

            // if only one user likes
            if ($likesCount == 1) {
                // check, if you liked
                if ($likes[$i]->user->guid != Yii::$app->user->guid) {
                    // output, if an other user liked
                    $userlist .= Html::encode($likes[$i]->user->displayName) . Yii::t('LikeModule.widgets_views_likeLink', ' likes this.');
                }
            } else {

                // check, if you liked
                if ($likes[$i]->user->guid != Yii::$app->user->guid) {
                    // output, if an other user liked
                    $userlist .= Html::encode($likes[$i]->user->displayName). "\n";
                }

                // check if exists more user as limited
                if ($i == $maxUser) {
                    // output with the number of not rendered users
                    $userlist .= Yii::t('LikeModule.widgets_views_likeLink', 'and {count} more like this.', array('{count}' => (int)(count($likes) - $maxUser)));

                    // stop the loop
                    break;
                }
            }
        }

        return $userlist;
    }

}

?>