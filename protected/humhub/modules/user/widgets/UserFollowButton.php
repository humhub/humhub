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
            if (Friendship::getStateForUser(Yii::$app->user->getIdentity(), $this->user)) {
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
        $this->followOptions['data-content-container-id'] = $this->user->id;
        $this->unfollowOptions['data-content-container-id'] = $this->user->id;

        // Add JS Action
        $this->followOptions['data-action-click'] = 'content.container.follow';
        $this->unfollowOptions['data-action-click'] = 'content.container.unfollow';

        // Add Action Url
        $this->followOptions['data-action-url'] = $this->user->createUrl('/user/profile/follow');
        $this->unfollowOptions['data-action-url'] = $this->user->createUrl('/user/profile/unfollow');

        // Add Action Url
        $this->followOptions['data-ui-loader'] = '';
        $this->unfollowOptions['data-ui-loader'] = '';

        $module = Yii::$app->getModule('user');

        // still enable unfollow if following was disabled afterwards.
        if ($module->disableFollow) {
            return Html::a($this->unfollowLabel, '#', $this->unfollowOptions);
        }

        return Html::a($this->unfollowLabel, '#', $this->unfollowOptions) .
                Html::a($this->followLabel, '#', $this->followOptions);
    }

}
