<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
?>

<?php if ($clickable): ?>
<a href="<?= \yii\helpers\Url::to(['/activity/link', 'id' => $record->id])?>">
<?php endif; ?>
    <li class="activity-entry" data-stream-entry data-action-component="activity.ActivityStreamEntry" data-content-key="<?= $record->content->id ?>">
        <div class="media">
            <?php if ($originator !== null) : ?>
                <!-- Show user image -->
                <img class="media-object img-rounded pull-left" data-src="holder.js/32x32" alt="32x32"
                     style="width: 32px; height: 32px;"
                     src="<?= $originator->getProfileImage()->getUrl(); ?>">
            <?php endif; ?>

            <!-- Show space image, if you are outside from a space -->
            <?php if (!Yii::$app->controller instanceof \humhub\modules\content\components\ContentContainerController): ?>
                <?php if ($record->content->container instanceof humhub\modules\space\models\Space): ?>
                    <?=
                    \humhub\modules\space\widgets\Image::widget([
                        'space' => $record->content->container,
                        'width' => 20,
                        'htmlOptions' => [
                            'class' => 'img-space pull-left',
                        ]
                    ]);
                    ?>
                <?php endif; ?>

            <?php endif; ?>

            <div class="media-body text-break">

                <!-- Show content -->
                <?= $content; ?><br/>

                <!-- show time -->
                <?= \humhub\widgets\TimeAgo::widget(['timestamp' => $record->content->created_at]); ?>
            </div>
        </div>
    </li>
<?php if ($clickable): ?></a>
<?php endif; ?>