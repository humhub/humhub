<?php

/** @var \humhub\modules\user\models\User $originator */
/** @var \humhub\modules\space\models\Space $space */
/** @var \humhub\modules\notification\models\Notification $record */
/** @var boolean $isNew */
/** @var string $content */

?>
<li class="<?php if ($isNew) : ?>new<?php endif; ?>">
    <a href="<?php echo $url; ?>">
        <div class="media">

            <!-- show user image -->
            <?php if ($originator !== null): ?>
                <img class="media-object img-rounded pull-left"
                     data-src="holder.js/32x32" alt="32x32"
                     style="width: 32px; height: 32px;"
                     src="<?php echo $originator->getProfileImage()->getUrl(); ?>" />
                 <?php endif; ?>

            <!-- show space image -->
            <?php if ($space !== null) : ?>
                <img class="media-object img-rounded img-space pull-left"
                     data-src="holder.js/20x20" alt="20x20"
                     style="width: 20px; height: 20px;"
                     src="<?php echo $space->getProfileImage()->getUrl(); ?>">
                 <?php endif; ?>

            <!-- show content -->
            <div class="media-body">

                <?php echo $content; ?>

                <br> <?php echo humhub\widgets\TimeAgo::widget(['timestamp' => $record->created_at]); ?> 
                <?php if ($isNew) : ?> <span class="label label-danger"><?php echo Yii::t('NotificationModule.views_notificationLayout', 'New'); ?></span><?php endif; ?>
            </div>

        </div>
    </a>
</li>
