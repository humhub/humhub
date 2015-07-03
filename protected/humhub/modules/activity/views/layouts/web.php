<?php if ($clickable): ?><a href="#" onClick="activityShowItem(<?= $record->id; ?>); return false;"><?php endif; ?>
    <li class="activity-entry">
        <div class="media">
            <?php if ($originator !== null) : ?>
                <!-- Show user image -->
                <img class="media-object img-rounded pull-left" data-src="holder.js/32x32" alt="32x32"
                     style="width: 32px; height: 32px;"
                     src="<?php echo $originator->getProfileImage()->getUrl(); ?>">
                 <?php endif; ?>

            <!-- Show space image, if you are outside from a space -->
            <?php if (!Yii::$app->controller instanceof \humhub\modules\content\components\ContentContainerController && $record->content->space !== null): ?>
                <img class="media-object img-rounded img-space pull-left" data-src="holder.js/20x20" alt="20x20"
                     style="width: 20px; height: 20px;"
                     src="<?php echo $record->content->space->getProfileImage()->getUrl(); ?>">
                 <?php endif; ?>

            <div class="media-body">

                <!-- Show content -->
                <?php echo $content; ?><br />

                <!-- show time -->
                <?php echo \humhub\widgets\TimeAgo::widget(['timestamp' => $record->content->created_at]); ?>
            </div>
        </div>
    </li>
    <?php if ($clickable): ?></a><?php endif; ?>