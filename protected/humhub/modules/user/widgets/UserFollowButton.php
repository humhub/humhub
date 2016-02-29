<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use Yii;
use yii\bootstrap\Html;
use humhub\modules\friendship\models\Friendship;

/**
 * UserFollowButton
 *
 * @author luke
 * @since 0.11
 */
class UserFollowButton extends \yii\base\Widget
{

    /**
     * @var \humhub\modules\user\models\User
     */
    public $user;

    /**
     * @var string label for follow button (optional)
     */
    public $followLabel = null;

    /**
     * @var string label for unfollow button (optional)
     */
    public $unfollowLabel = null;

    /**
     * @var string options for follow button 
     */
    public $followOptions = ['class' => 'btn btn-primary'];

    /**
     * @var array options for unfollow button 
     */
    public $unfollowOptions = ['class' => 'btn btn-info'];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->followLabel === null) {
            $this->followLabel = Yii::t("UserModule.widgets_views_followButton", "Follow");
        }
        if ($this->unfollowLabel === null) {
            $this->unfollowLabel = Yii::t("UserModule.widgets_views_followButton", "Unfollow");
        }

        if (!isset($this->followOptions['class'])) {
            $this->followOptions['class'] = "";
        }
        if (!isset($this->unfollowOptions['class'])) {
            $this->unfollowOptions['class'] = "";
        }

        if (!isset($this->followOptions['style'])) {
            $this->followOptions['style'] = "";
        }
        if (!isset($this->unfollowOptions['style'])) {
            $this->unfollowOptions['style'] = "";
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->user->isCurrentUser() || \Yii::$app->user->isGuest) {
            return;
        }

        if (Yii::$app->getModule('friendship')->getIsEnabled()) {
            // Don't show follow button, when friends
            if (Friendship::getFriendsQuery($this->user)->one() !== null) {
                return;
            }
        }

        // Add class for javascript handling
        $this->followOptions['class'] .= ' followButton';
        $this->unfollowOptions['class'] .= ' unfollowButton';

        // Hide inactive button
        if ($this->user->isFollowedByUser()) {
            $this->followOptions['style'] .= ' display:none;';
        } else {
            $this->unfollowOptions['style'] .= ' display:none;';
        }

        // Add UserId Buttons
        $this->followOptions['data-userid'] = $this->user->id;
        $this->unfollowOptions['data-userid'] = $this->user->id;


        $this->view->registerJsFile('@web/resources/user/followButton.js');

        return Html::a($this->unfollowLabel, $this->user->createUrl('/user/profile/unfollow'), $this->unfollowOptions) .
                Html::a($this->followLabel, $this->user->createUrl('/user/profile/follow'), $this->followOptions);
    }

}
