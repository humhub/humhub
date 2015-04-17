
<style type="text/css">
  body {margin: 0; padding: 0; }
  div.container { width: 100%; margin: 0; padding: 0; }
</style>

<div class="comotion-profile comotion-profile-header">
  <img src="<?php echo $user->getProfileImage()->getUrl(); ?>" style="width: 100%;">
  <?php if (!$isProfileOwner) { ?>
    <div>Match</div>
  <?php } ?>
  <div class="comotion-profile-data">
    <h1><?php echo CHtml::encode($user->displayName); ?></h1>
    <h3><?php echo CHtml::encode($user->profile->title); ?></h3>
  </div>
</div>
