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
            <a href="<?php echo $space->getUrl(); ?>">
                <img src="<?php echo $space->getProfileImage()->getUrl(); ?>" class="img-rounded tt img_margin"
                     height="40" width="40" alt="40x40" data-src="holder.js/40x40"
                     style="width: 40px; height: 40px;"
                     data-toggle="tooltip" data-placement="top" title=""
                     data-original-title="<strong><?php echo Html::encode($space->name); ?></strong>">
            </a>
        <?php endforeach; ?>

        <?php if ($showMoreButton): ?>
            <br />
            <br />
            <?php echo Html::a(Yii::t('DirectoryModule.widgets_views_newSpaces', 'See all'), array('/directory/directory/spaces'), array('class'=>'btn btn-xl btn-primary')); ?>
        <?php endif; ?>
    </div>
</div>