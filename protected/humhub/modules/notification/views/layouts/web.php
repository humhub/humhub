<?php

use humhub\widgets\TimeAgo;
use yii\helpers\Html;

/** @var \humhub\modules\user\models\User $originator */
/** @var \humhub\modules\space\models\Space $space */
/** @var \humhub\modules\notification\models\Notification $record */
/** @var boolean $isNew */
/** @var string $content */
/** @var string $url */
/** @var string $relativeUrl */

?>
<li class="<?php if ($isNew) : ?>new<?php endif; ?>"
    data-notification-id="<?= $record->id ?>"
    data-notification-group="<?= !empty($record->baseModel->getGroupkey()) ? Html::encode(get_class($record->baseModel)).':'.Html::encode($record->baseModel->getGroupKey()) : '' ?>">

    <a href="<?= isset($relativeUrl) ? $relativeUrl : $url; ?>">
        <div class="media">

            <!-- show user image -->
            <?php if ($originator): ?>
                <img class="media-object img-rounded pull-left"
                     data-src="holder.js/32x32" alt="32x32"
                     style="width: 32px; height: 32px;"
                     src="<?php echo $originator->getProfileImage()->getUrl(); ?>" />
                 <?php endif; ?>

            <!-- show space image -->
            <?php if ($space) : ?>
                <img class="media-object img-rounded img-space pull-left"
                     data-src="holder.js/20x20" alt="20x20"
                     style="width: 20px; height: 20px;"
                     src="<?= $space->getProfileImage()->getUrl(); ?>">
                 <?php endif; ?>

            <!-- show content -->
            <div class="media-body">

                <?= $content; ?>

                <br> <?= TimeAgo::widget(['timestamp' => $record->created_at]); ?>
                <?php if ($isNew) : ?>
                    <span class="label label-danger"><?= Yii::t('NotificationModule.base', 'New'); ?></span>
                <?php endif; ?>
            </div>

        </div>
    </a>
</li>
