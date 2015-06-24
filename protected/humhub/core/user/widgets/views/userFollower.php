<?php
$followers = $user->getFollowers(array('limit' => 16));
?>
<?php if (count($followers) > 0) { ?>
    <div class="panel panel-default follower" id="profile-follower-panel">

        <!-- Display panel menu widget -->
        <?php $this->widget('application.widgets.PanelMenuWidget', array('id' => 'profile-follower-panel')); ?>

        <div class="panel-heading"><?php echo Yii::t('UserModule.widgets_views_userFollower', '<strong>User</strong> followers'); ?></div>

        <div class="panel-body">
            <?php foreach ($followers as $follower): ?>
                <a href="<?php echo $follower->getProfileUrl(); ?>">
                    <img src="<?php echo $follower->getProfileImage()->getUrl(); ?>" class="img-rounded tt img_margin"
                         height="24" width="24" alt="24x24" data-src="holder.js/24x24"
                         style="width: 24px; height: 24px;"
                         data-toggle="tooltip" data-placement="top" title=""
                         data-original-title="<strong><?php echo CHtml::encode($follower->displayName); ?></strong><br><?php echo CHtml::encode($follower->profile->title); ?>">
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php } ?>

<?php
$following = $user->getFollowingObjects('User', array('limit' => 16));
?>
<?php if (count($following) > 0) { ?>
    <div class="panel panel-default follower" id="profile-following-panel">

        <!-- Display panel menu widget -->
        <?php $this->widget('application.widgets.PanelMenuWidget', array('id' => 'profile-following-panel')); ?>


        <div class="panel-heading">
            <?php echo Yii::t('UserModule.widgets_views_userFollower', '<strong>Following</strong> user'); ?>
        </div>

        <div class="panel-body">
            <?php foreach ($following as $followingUser): ?>
                <a href="<?php echo $followingUser->getProfileUrl(); ?>">
                    <img src="<?php echo $followingUser->getProfileImage()->getUrl(); ?>" class="img-rounded tt img_margin"
                         height="24" width="24" alt="24x24" data-src="holder.js/24x24"
                         style="width: 24px; height: 24px;"
                         data-toggle="tooltip" data-placement="top" title=""
                         data-original-title="<strong><?php echo CHtml::encode($followingUser->displayName); ?></strong><br><?php echo CHtml::encode($followingUser->profile->title); ?>">
                </a>
            <?php endforeach; ?>
        </div>
    </div>

<?php } ?>