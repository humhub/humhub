<?php

/* @var $space Space */

use humhub\helpers\Html;
use humhub\libs\Helpers;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image;
use humhub\widgets\bootstrap\Badge;

?>

<a
    href="<?= $space->getUrl() ?>"
    class="dropdown-item d-flex<?= $visible ? '' : ' d-none' ?>"
    data-space-chooser-item <?= $data ?>
    data-space-guid="<?= $space->guid ?>">

    <div class="flex-shrink-0 me-2">
        <?= Image::widget(['space' => $space, 'width' => 24]) ?>
    </div>
    <div class="flex-grow-1">
        <strong class="space-name"><?= Html::encode($space->name) ?></strong>
        <?= $badge ?>
        <div data-message-count="<?= $updateCount ?>"
             class="badge badge-space messageCount float-end tt d-none"
             title="<?= Yii::t('SpaceModule.chooser', '{n,plural,=1{# new entry} other{# new entries}} since your last visit', ['n' => $updateCount]) ?>">
            <?= $updateCount ?>
        </div>
        <br>
        <p class="space-description"><?= Html::encode(Helpers::truncateText($space->description, 60)) ?></p>
        <?php if ($space->hasTags()) : ?>
            <div class="space-tags d-none">
                <?php foreach ($space->getTags() as $tag) : ?>
                    <?= Badge::secondary($tag) . ' ' ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</a>
