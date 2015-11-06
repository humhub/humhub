<?php
use yii\helpers\Html;

?>

<div class="panel panel-default spaces" id="new-spaces-panel">

    <!-- Display panel menu widget -->
    <?php echo humhub\widgets\PanelMenu::widget(array('id' => 'new-spaces-panel')); ?>

    <div class="panel-heading">
        <?php echo Yii::t('DirectoryModule.widgets_views_spaceStats', '<strong>New</strong> spaces'); ?>
    </div>
    <div class="panel-body">
        <?php foreach ($newSpaces as $space) : ?>
            <a href="<?php echo $space->getUrl(); ?>" class="tt" data-toggle="tooltip" data-placement="top" title=""
               data-original-title="<?php echo Html::encode($space->name); ?>">
                <?php echo \humhub\modules\space\widgets\SpaceImage::widget(['space' => $space, 'width' => 40, 'height' => 40, 'cssAcronymClass' => 'new-spaces', 'cssImageClass' => 'img_margin']); ?>
            </a>
        <?php endforeach; ?>

        <?php if ($showMoreButton): ?>
            <br/>
            <br/>
            <?php echo Html::a(Yii::t('DirectoryModule.widgets_views_newSpaces', 'See all'), array('/directory/directory/spaces'), array('class' => 'btn btn-xl btn-primary')); ?>
        <?php endif; ?>
    </div>
</div>