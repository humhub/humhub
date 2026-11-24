<?php

use humhub\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image;
use humhub\widgets\PanelMenu;

/* @var $newSpaces Space[] */
/* @var $showMoreButton bool */
?>

<div class="card content-card spaces" id="new-spaces-panel">

    <!-- Display panel menu widget -->
    <?= PanelMenu::widget() ?>

    <div class="card-header">
        <div class="card-title"><?= Yii::t('SpaceModule.base', '<strong>New</strong> spaces') ?></div>
    </div>
    <div class="card-body">
        <?php foreach ($newSpaces as $space) : ?>
            <?= Image::widget([
                'space' => $space,
                'showTooltip' => true,
                'width' => 40,
                'link' => true,
                'htmlOptions' => [
                    'style' => 'margin-bottom: 5px;',
                ],
            ]) ?>
        <?php endforeach; ?>

        <?php if ($showMoreButton): ?>
            <br/>
            <br/>
            <?= Html::a(Yii::t('SpaceModule.base', 'See all'), ['/space/spaces'], ['class' => 'btn btn-xl btn-primary']) ?>
        <?php endif; ?>
    </div>
</div>
