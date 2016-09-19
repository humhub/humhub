<?php

use yii\helpers\Html;
?>

<div class="panel panel-default members" id="space-members-panel">
    <?php echo \humhub\widgets\PanelMenu::widget(['id' => 'space-members-panel']); ?>
    <div class="panel-heading"><?php echo Yii::t('SpaceModule.widgets_views_spaceMembers', '<strong>Space</strong> members'); ?></div>
    <div class="panel-body">
        <?php foreach ($users as $user) : ?>
            <a href="<?php echo $user->getUrl(); ?>">
                <img src="<?php echo $user->getProfileImage()->getUrl(); ?>" class="img-rounded tt img_margin"
                     height="24" width="24" alt="24x24" data-src="holder.js/24x24"
                     style="width: 24px; height: 24px;" data-toggle="tooltip" data-placement="top" title=""
                     data-original-title="<?php echo Html::encode($user->displayName); ?>">
            </a>
        <?php endforeach; ?>
        <?php if (count($users) == $maxMembers) : ?>
            <br>
            <a href="<?php echo $space->createUrl('/space/membership/members-list'); ?>" data-target="#globalModal" class="btn btn-default btn-sm"><?php echo Yii::t('SpaceModule.widgets_views_spaceMembers', 'Show all'); ?></a>
        <?php endif; ?>
    </div>
</div>