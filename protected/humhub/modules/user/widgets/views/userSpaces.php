<?php

use humhub\helpers\Html;
use humhub\modules\space\widgets\Image;
use humhub\widgets\PanelMenu;

?>
<?php if (count($spaces) > 0) : ?>
    <div id="user-spaces-panel" class="panel panel-default members" style="position: relative;">

        <!-- Display panel menu widget -->
        <?= PanelMenu::widget(['id' => 'user-spaces-panel']) ?>

        <div class="panel-heading">
            <?= Yii::t('UserModule.base', '<strong>Member</strong> of these Spaces') ?>
        </div>

        <div class="panel-body d-flex gap-2 flex-wrap">
            <?php foreach ($spaces as $space): ?>
                <?= Image::widget([
                    'space' => $space,
                    'width' => 30,
                    'link' => true,
                    'showTooltip' => true,
                ]) ?>
            <?php endforeach; ?>

            <?php if ($showMoreLink): ?>
                <br>
                <br>
                <div class="clearfix">
                    <?= Html::a('Show all', $user->createUrl('/user/profile/space-membership-list'), ['class' => 'float-end btn btn-sm btn-light', 'data-bs-target' => '#globalModal']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
