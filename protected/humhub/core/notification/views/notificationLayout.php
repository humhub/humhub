<?php if (!isset($this->contentOnly) || !$this->contentOnly) { ?>
    <li class="<?php if (!$notification->seen) : ?>new<?php endif; ?>">
        <a href="<?php echo $notification->getUrl(); ?>">
            <div class="media">

                <!-- show user image -->
                <?php if ($notification->creator !== null && empty($hideUserImage)): ?>
                    <img class="media-object img-rounded pull-left"
                         data-src="holder.js/32x32" alt="32x32"
                         style="width: 32px; height: 32px;"
                         src="<?php echo $notification->creator->getProfileImage()->getUrl(); ?>">
                     <?php endif; ?>

                <!-- show space image -->
                <?php if (empty($hideSpaceImage) && $notification->space != null) : ?>
                    <img class="media-object img-rounded img-space pull-left"
                         data-src="holder.js/20x20" alt="20x20"
                         style="width: 20px; height: 20px;"
                         src="<?php echo $notification->space->getProfileImage()->getUrl(); ?>">
                     <?php endif; ?>

                <!-- show content -->
                <div class="media-body">

                    <?php echo $content; ?>

                    <br><span class="time"
                              title="<?php echo $notification->created_at; ?>"><?php echo $notification->created_at; ?></span>
                    <?php if (!$notification->seen) : ?> <span class="label label-danger"><?php echo Yii::t('NotificationModule.views_notificationLayout', 'New'); ?></span><?php endif; ?>
                </div>

            </div>
        </a>
        <?php if (isset($iconClass) && $iconClass != ""): ?>
            <i class="<?php echo $iconClass; ?>"></i>
        <?php endif; ?>
    </li>
<?php
}
else {
    echo $content;
}
?>
