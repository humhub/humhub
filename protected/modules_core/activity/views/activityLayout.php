<?php
/**
 * This view represents the basic layout of an activity entry.
 *
 * @property ActivityWidget $this the widget which shows the activity
 * @property Activity $activity the current activity
 *
 * @package humhub.modules.activity
 * @since 0.5
 */
/* * @var $activity Activity */
?>


<li class="activity-entry">
    <?php if ($this->wallEntryId != 0) : ?><a href="#" onClick="activityShowItem(<?php echo $this->wallEntryId; ?>);
                return false;"><?php endif; ?>
        <div class="media">

            <?php if ($activity->content->user !== null) : ?>
                <!-- Show user image -->
                <img class="media-object img-rounded pull-left" data-src="holder.js/32x32" alt="32x32"
                     style="width: 32px; height: 32px;"
                     src="<?php echo $activity->content->user->getProfileImage()->getUrl(); ?>">
                 <?php endif; ?>

            <!-- Show space image, if you are outside from a space -->
            <?php if (!Yii::app()->controller instanceof ContentContainerController && $activity->content->space !== null): ?>
                <img class="media-object img-rounded img-space pull-left" data-src="holder.js/20x20" alt="20x20"
                     style="width: 20px; height: 20px;"
                     src="<?php echo $activity->content->space->getProfileImage()->getUrl(); ?>">
                 <?php endif; ?>

            <div class="media-body">

                <!-- Show content -->
                <?php echo $content; ?><br>

                <!-- show time -->
                <?php echo HHtml::timeago($activity->content->created_at); ?>
            </div>
        </div>
        <?php if ($this->wallEntryId != 0) : ?></a><?php endif; ?>
</li>

