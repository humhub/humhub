<?php

use humhub\modules\notification\models\Notification;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image as SpaceImage;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\Image as UserImage;
use humhub\widgets\bootstrap\Html;
use humhub\widgets\TimeAgo;

/** @var User $originator */
/** @var Space $space */
/** @var Notification $record */
/** @var bool $isNew */
/** @var string $content */
/** @var string $url */
/** @var string $relativeUrl */
?>

<a
    class="d-flex<?= $isNew ? ' new' : '' ?>"
    href="<?= $relativeUrl ?? $url ?>"
    data-notification-id="<?= $record->id ?>"
    data-notification-group="<?= !empty($record->baseModel->getGroupkey())
        ? Html::encode(get_class($record->baseModel) . ':' . $record->baseModel->getGroupKey())
        : '' ?>">
    <div class="flex-shrink-0">
        <?php if ($originator) : ?>
            <?= UserImage::widget([
                'user' => $originator,
                'width' => 32,
                'link' => false,
                'hideOnlineStatus' => true,
            ]) ?>
        <?php endif; ?>
        <?php if ($space instanceof Space) : ?>
            <?= SpaceImage::widget([
                'space' => $space,
                'width' => 20,
                'link' => false,
                'htmlOptions' => ['class' => 'img-space'],
            ]) ?>
        <?php endif; ?>
    </div>
    <div class="flex-grow-1">
        <?= $content ?>
        <br>
        <?= TimeAgo::widget(['timestamp' => $record->created_at]) ?>
    </div>
    <div class="flex-shrink-0 order-last text-center">
        <?php if ($isNew) : ?>
            <span class="badge-new"></span>
        <?php endif; ?>
    </div>
</a>
