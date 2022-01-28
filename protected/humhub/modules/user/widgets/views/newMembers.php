<?php

use humhub\modules\user\models\User;
use humhub\widgets\Button;
use humhub\widgets\ModalButton;
use humhub\widgets\PanelMenu;
use humhub\modules\user\widgets\Image;

/* @var $newUsers User[] */
/* @var $showInviteButton boolean */
/* @var $showMoreButton boolean */
?>
<div class="panel panel-default members" id="new-people-panel">
    <?= PanelMenu::widget(['id' => 'new-people-panel']) ?>

    <div class="panel-heading">
        <?= Yii::t('UserModule.base', '<strong>New</strong> people') ?>
    </div>
    <div class="panel-body">
        <?php foreach ($newUsers as $user) : ?>
            <?= Image::widget(['user' => $user, 'width' => 40, 'showTooltip' => true]) ?>
        <?php endforeach; ?>

        <?php if ($showInviteButton || $showMoreButton): ?>
            <hr />
        <?php endif; ?>

        <?php if ($showInviteButton): ?>
            <?= ModalButton::primary(Yii::t('UserModule.base', 'Send invite'))
                ->load(['/user/invite'])->icon('invite')->sm() ?>
        <?php endif; ?>
        <?php if ($showMoreButton): ?>
            <?= Button::primary(Yii::t('UserModule.base', 'See all'))
                ->link(['/user/people'])->icon('list-ul')->sm() ?>
        <?php endif; ?>

    </div>
</div>
