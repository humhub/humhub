<?php
use yii\helpers\Html;

?>

<div class="panel panel-default spaces" id="new-spaces-panel">

    <!-- Display panel menu widget -->
    <?php echo humhub\widgets\PanelMenu::widget(['id' => 'new-spaces-panel']); ?>

    <div class="panel-heading">
        <?php echo Yii::t('DirectoryModule.base', '<strong>New</strong> spaces'); ?>
    </div>
    <div class="panel-body">
        <?php foreach ($newSpaces as $space) : ?>
            <?= \humhub\modules\space\widgets\Image::widget([
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
            <?php echo Html::a(Yii::t('DirectoryModule.base', 'See all'), ['/directory/directory/spaces'], ['class' => 'btn btn-xl btn-primary']); ?>
        <?php endif; ?>
    </div>
</div>