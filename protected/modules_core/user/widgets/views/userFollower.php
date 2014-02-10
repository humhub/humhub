<?php
/**
 * This View shows a list of followers / following members inside user profile sidebar.
 *
 * @property Array $follower contains users which are followers. (Max. 30 Users)
 * @property Array $follow contains all members which the user is following. (Max. 30 Users)
 *
 * @package humhub.modules_core.user.widget
 * @since 0.5
 */
?>

<?php if (count($follower) > 0) { ?>
    <div class="panel panel-default follower">

        <div class="panel-heading"><?php echo Yii::t('UserModule.base', 'Followers'); ?></div>

        <div class="panel-body">
            <?php foreach ($follower as $user): ?>
                <a href="<?php echo $user->getProfileUrl(); ?>">
                    <img src="<?php echo $user->getProfileImage()->getUrl(); ?>" class="img-rounded tt img_margin"
                         height="24" width="24" alt="24x24" data-src="holder.js/24x24"
                         style="width: 24px; height: 24px;"
                         data-toggle="tooltip" data-placement="top" title=""
                         data-original-title="<strong><?php echo $user->displayName; ?></strong><br><?php echo $user->title; ?>">
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php } ?>

<?php if (count($follow) > 0) { ?>
    <div class="panel panel-default follower">

        <div class="panel-heading">
            <?php echo Yii::t('UserModule.base', 'Following'); ?>
        </div>

        <div class="panel-body">
            <?php foreach ($follow as $user): ?>
                <a href="<?php echo $user->getProfileUrl(); ?>">
                    <img src="<?php echo $user->getProfileImage()->getUrl(); ?>" class="img-rounded tt img_margin"
                         height="24" width="24" alt="24x24" data-src="holder.js/24x24"
                         style="width: 24px; height: 24px;"
                         data-toggle="tooltip" data-placement="top" title=""
                         data-original-title="<strong><?php echo $user->displayName; ?></strong><br><?php echo $user->title; ?>">
                </a>
            <?php endforeach; ?>
        </div>
    </div>

<?php } ?>