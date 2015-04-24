
<!-- CONTENT STARTS HERE -->
<!-- CoMotion Connections -->
<?php
$this->widget('application.modules_core.user.widgets.RecommendationWidget', array('user' => $this->getUser()));
?>

<div class="comotion-content comotion-events-placeholder">
  <h3>Events</h3>
  <h4><?php echo $this->getUser()->displayName ?> is attending:</h4>
  <img src="<?php echo Yii::app()->theme->baseUrl ?>/img/fake_events_360.png" />
</div>


<!-- interests -->
<?php
  $this->widget('application.modules_core.user.widgets.userTagsWidget', array('user' => $this->getUser()));
?>

<!-- activity creation and activity stream -->
  <div class="user-profile-activities">
  <h3><?php echo $this->getUser()->displayName ?>'s Activities</h3>
  <?php

  $this->widget('application.modules_core.post.widgets.PostFormWidget', array('contentContainer' => $this->getUser()));

  ?>


  <?php
  $this->widget('application.modules_core.wall.widgets.StreamWidget', array(
      'contentContainer' => $this->getUser(),
      'streamAction' => '//user/profile/stream',
      'messageStreamEmpty' => ($this->getUser()->canWrite()) ?
              Yii::t('UserModule.views_profile_index', '<b>Your profile stream is still empty</b><br>Get started and post something...') :
              Yii::t('UserModule.views_profile_index', '<b>This profile stream is still empty!</b>'),
      'messageStreamEmptyCss' => ($this->getUser()->canWrite()) ?
              'placeholder-empty-stream' :
              '',
  ));

  ?>
</div>