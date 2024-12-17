<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image;
use humhub\modules\space\widgets\SpaceDirectoryActionButtons;
use humhub\modules\space\widgets\SpaceDirectoryIcons;
use humhub\modules\space\widgets\SpaceDirectoryStatus;
use humhub\modules\space\widgets\SpaceDirectoryTagList;
use yii\web\View;

/* @var $this View */
/* @var $space Space */
?>

<div class="card-panel<?= $space->isArchived() ? ' card-archived' : '' ?>" data-space-id="<?= $space->id ?>" data-space-guid="<?= $space->guid ?>">
    <div
        class="card-bg-image"<?= $space->getProfileBannerImage()->hasImage() ? ' style="background-image: url(\'' . $space->getProfileBannerImage()->getUrl() . '\')"' : '' ?>></div>
    <div class="card-header">
        <a href="<?= $space->getUrl() ?>" class="card-space-link">
            <?= Image::widget([
                'space' => $space,
                'width' => 94,
            ]) ?>
            <?= SpaceDirectoryStatus::widget(['space' => $space]) ?>
        </a>
        <div class="card-icons">
            <?= SpaceDirectoryIcons::widget(['space' => $space]) ?>
        </div>
    </div>
    <div class="card-body">
        <a href="<?= $space->getUrl() ?>" class="card-space-link">
            <strong class="card-title"><?= Html::encode($space->name) ?></strong>
            <?php if (trim($space->description) !== '') : ?>
                <div class="card-details"><?= Html::encode($space->description) ?></div>
            <?php endif; ?>
        </a>
        <?= SpaceDirectoryTagList::widget([
            'space' => $space,
            'template' => '<div class="card-tags">{tags}</div>',
        ]) ?>
    </div>
    <?= SpaceDirectoryActionButtons::widget([
        'space' => $space,
        'template' => '<div class="card-footer">{buttons}</div>',
    ]) ?>
</div>
