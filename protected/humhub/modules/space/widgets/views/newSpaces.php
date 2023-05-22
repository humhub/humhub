<?php

use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image;
use humhub\widgets\PanelMenu;
use yii\helpers\Html;

/* @var $newSpaces Space[] */
/* @var $showMoreButton boolean */
?>

<div class="panel panel-default spaces" id="new-spaces-panel">

    <!-- Display panel menu widget -->
    <?= PanelMenu::widget(['id' => 'new-spaces-panel']); ?>

    <div class="panel-heading">
        <?= Yii::t('SpaceModule.base', '<strong>New</strong> spaces'); ?>
    </div>
    <div class="panel-body">
        <?php foreach ($newSpaces as $space) : ?>
            <?= Image::widget([
                'space' => $space,
                'showTooltip' => true,
                'width' => 40,
                'link' => true,
                'htmlOptions' => [
                    'style' => 'margin-bottom: 5px;',
                ]
            ]); ?>
        <?php endforeach; ?>

        <?php if ($showMoreButton): ?>
            <br/>
            <br/>
            <?= Html::a(Yii::t('SpaceModule.base', 'See all'), ['/space/spaces'], ['class' => 'btn btn-xl btn-primary']); ?>
        <?php endif; ?>
    </div>
</div>