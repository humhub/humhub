<?php

use humhub\widgets\Button;
use humhub\widgets\ModalButton;
use humhub\widgets\PanelMenu;
use humhub\modules\user\widgets\Image;

/* @var $newUsers \yii\db\ActiveQuery */
/* @var $showInviteButton boolean */
/* @var $showMoreButton boolean */
?>
<div class="panel panel-default members" id="new-people-panel">
    <?= PanelMenu::widget(['id' => 'new-people-panel']) ?>

    <div class="panel-heading">
        <?= Yii::t('DirectoryModule.base', '<strong>New</strong> people') ?>
    </div>
    <div class="panel-body">
        <?php foreach ($newUsers->limit(10)->all() as $user) : ?>
            <?= Image::widget(['user' => $user, 'width' => 40, 'showTooltip' => true]) ?>
        <?php endforeach; ?>

        <?php if ($showInviteButton || $showMoreButton): ?>
            <hr />
        <?php endif; ?>

        <?php if ($showInviteButton): ?>
            <?= ModalButton::primary(Yii::t('DirectoryModule.base', 'Send invite'))
                ->load(['/user/invite'])->icon('invite')->sm() ?>
        <?php endif; ?>
        <?php if ($showMoreButton): ?>
            <?= Button::primary(Yii::t('DirectoryModule.base', 'See all'))
                ->link(['/directory/directory/members'])->icon('list-ul')->sm() ?>
        <?php endif; ?>

    </div>
</div>
