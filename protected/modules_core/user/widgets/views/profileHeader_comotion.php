<div class="comotion-profile comotion-profile-header">
  <!-- inline style! TODO: refactor to css -->
  <!-- TODO: port user-photo upload so user can add photo! -->
  <img  class="profile-user-photo" id="user-profile-image"
        src="<?php echo $user->getProfileImage()->getUrl(); ?>"
        />
  <?php if (!$isProfileOwner) { ?>
    <div>XX%</div>
  <?php } ?>
  <div class="comotion-profile-data">
    <h1><?php echo CHtml::encode($user->displayName); ?></h1>
    <h2><?php echo CHtml::encode($user->profile->title); ?></h2>
  </div>
</div>
