<?php

use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image;
use humhub\modules\user\models\User;
use humhub\widgets\modal\ModalButton;
use humhub\widgets\PanelMenu;

/* @var $spaces Space[] */
/* @var $user User */
/* @var $showMoreLink bool */
?>
<?php if (count($spaces) > 0) : ?>
    <div id="user-spaces-panel" class="panel panel-default members">
        <?= PanelMenu::widget() ?>
        <div class="panel-heading">
            <?= Yii::t('UserModule.base', '<strong>Member</strong> of these Spaces') ?>
        </div>
        <div class="panel-body">
            <div class="d-flex gap-2 flex-wrap">
            <?php foreach ($spaces as $space): ?>
                <?= Image::widget([
                    'space' => $space,
                    'width' => 30,
                    'link' => true,
                    'showTooltip' => true,
                ]) ?>
            <?php endforeach; ?>
            </div>

            <?php if ($showMoreLink): ?>
                <div class="clearfix mt-3">
                    <?= ModalButton::light(Yii::t('UserModule.base', 'Show all'))
                        ->load($user->createUrl('/user/profile/space-membership-list'))
                        ->right()
                        ->sm() ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
