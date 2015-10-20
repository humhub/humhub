<?php

use yii\helpers\Url;
use yii\helpers\Html;
?>
<div class="panel panel-default members" id="new-people-panel">
    <!-- Display panel menu widget -->
    <?php echo \humhub\widgets\PanelMenu::widget(array('id' => 'new-people-panel')); ?>

    <div class="panel-heading">
        <?php echo Yii::t('DirectoryModule.widgets_views_memberStats', '<strong>New</strong> people'); ?>
    </div>
    <div class="panel-body">
        <?php foreach ($newUsers as $user) : ?>
            <a href="<?php echo $user->getUrl(); ?>">
                <img src="<?php echo $user->getProfileImage()->getUrl(); ?>" class="img-rounded tt img_margin"
                     height="40" width="40" alt="40x40" data-src="holder.js/40x40" style="width: 40px; height: 40px;"
                     data-toggle="tooltip" data-placement="top" title=""
                     data-original-title="<?php echo Html::encode($user->displayName); ?>">
            </a>
        <?php endforeach; ?>

        <?php if ($showMoreButton): ?>
            <br />
            <br />
            <?php echo Html::a(Yii::t('DirectoryModule.widgets_views_newMembers', 'See all'), Url::to(['/directory/directory/members']), array('class' => 'btn btn-xl btn-primary')); ?>
        <?php endif; ?>
    </div>
</div>