<?php

/**
 * RecommendationWidget displays:
 *  - if viewing current user's profile, a list of users to follow
 *  - if viewing another user's profile, that user's compatibility
 *
 * @package humhub.modules_core.user.widget
 * @since 0.11
 * @author christembreull@roosterpark.com
 */

class RecommendationWidget extends HWidget
{
  public $user;
  public $isProfileOwner;
  public $template;

  public function init() {
    // Guest users should never see this widget content
    if (Yii::app()->user->isGuest) {
      return;
    }

    $this->isProfileOwner = (Yii::app()->user->id == $this->user->id);
  }

  public function run() {

    if ($this->isProfileOwner) {
      // display the "which users match me best" view
      $this->render('recommendedUsers', array('user' => $this->user));
    } else {
      // display the "how this user's network matches me" view
      $this->render('userMatchStrength', array('user' => $this->user, 'out_user' => Yii::app()->user));
    }

  }
}
