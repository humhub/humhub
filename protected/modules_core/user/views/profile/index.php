
<!-- CONTENT STARTS HERE -->
<?php
$this->widget('application.modules_core.user.widgets.RecommendationWidget', array('user' => $this->getUser()));
?>

<div class="comotion-content comotion-events-placeholder">
  <h3>Events</h3>
  <h4><?php echo $this->getUser()->displayName ?> is attending:</h4>
  <img src="<?php echo Yii::app()->theme->baseUrl ?>/img/fake_events_360.png" />
</div>

<?php
$this->widget('application.modules_core.post.widgets.PostFormWidget', array('contentContainer' => $this->getUser()));
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

<div class="comotion-content comotion-interests-placeholder">
  <h3>Interests</h3>
  <div>Interests</div>
</div>
