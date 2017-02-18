<?php
use yii\helpers\Html;

?>

<div class="panel panel-default spaces" id="new-spaces-panel">

    <!-- Display panel menu widget -->
    <?php echo humhub\widgets\PanelMenu::widget(array('id' => 'new-spaces-panel')); ?>

    <div class="panel-heading">
        <?php echo Yii::t('DirectoryModule.base', '<strong>New</strong> spaces'); ?>
    </div>
    <div class="panel-body">
        <?php foreach ($newSpaces as $space) : ?>
            <?php echo \humhub\modules\space\widgets\Image::widget([
                'space' => $space,
                'width' => 40,
                'link' => true,
                'htmlOptions' => [
                    'style' => 'margin-bottom: 5px;',
                ],
                'linkOptions' => [
                    'class' => 'tt',
                    'data-toggle' => 'tooltip',
                    'data-placement' => 'top',
                    'title' => Html::encode($space->name),
                ]
            ]); ?>
        <?php endforeach; ?>

        <?php if ($showMoreButton): ?>
            <br/>
            <br/>
            <?php echo Html::a(Yii::t('DirectoryModule.base', 'See all'), array('/directory/directory/spaces'), array('class' => 'btn btn-xl btn-primary')); ?>
        <?php endif; ?>
    </div>
</div>