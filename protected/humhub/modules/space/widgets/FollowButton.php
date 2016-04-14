<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use Yii;
use yii\bootstrap\Html;

/**
 * UserFollowButton
 *
 * @author luke
 * @since 0.11
 */
class FollowButton extends \yii\base\Widget
{

    /**
     * @var \humhub\modules\user\models\User
     */
    public $space;

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
    public $followOptions = ['class' => 'btn btn-primary btn-sm'];

    /**
     * @var array options for unfollow button 
     */
    public $unfollowOptions = ['class' => 'btn btn-primary btn-sm'];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->followLabel === null) {
            $this->followLabel = Yii::t('SpaceModule.widgets_views_followButton', "Follow");
        }
        if ($this->unfollowLabel === null) {
            $this->unfollowLabel = Yii::t('SpaceModule.widgets_views_followButton', "Unfollow");
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
        if (Yii::$app->user->isGuest || $this->space->isMember()) {
            return;
        }

        // Add class for javascript handling
        $this->followOptions['class'] .= ' followSpaceButton';
        $this->unfollowOptions['class'] .= ' unfollowSpaceButton';

        // Hide inactive button
        if ($this->space->isFollowedByUser()) {
            $this->followOptions['style'] .= ' display:none;';
        } else {
            $this->unfollowOptions['style'] .= ' display:none;';
        }

        // Add UserId Buttons
        $this->followOptions['data-spaceid'] = $this->space->id;
        $this->unfollowOptions['data-spaceid'] = $this->space->id;


        $this->view->registerJsFile('@web/resources/space/followButton.js');

        return Html::a($this->unfollowLabel, $this->space->createUrl('/space/space/unfollow'), $this->unfollowOptions) .
                Html::a($this->followLabel, $this->space->createUrl('/space/space/follow'), $this->followOptions);
    }

}
