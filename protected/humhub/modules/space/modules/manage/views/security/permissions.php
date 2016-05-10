<?php

use humhub\modules\user\widgets\PermissionGridEditor;
use humhub\modules\space\modules\manage\widgets\SecurityTabMenu;

?>

<div class="panel panel-default">
    <div>
        <div class="panel-heading">
            <?php echo Yii::t('SpaceModule.views_settings', '<strong>Security</strong> settings'); ?>
        </div>
    </div>
    
    <?= SecurityTabMenu::widget(['space' => $space]); ?>
    
    <div class="panel-body">
    <p class="help-block"><?php echo Yii::t('SpaceModule.views_settings', 'Permissions are assigned to different user-roles. To edit a permission, select the user-role you want to edit and change the drop-down value of the given permission.'); ?></p>
    </div>
    
    <ul id="tabs" class="nav nav-tabs tab-sub-menu">
            <?php foreach($groups as $currentGroupId => $groupLabel):?>
            <li class="<?= ($groupId === $currentGroupId) ? 'active' : '' ?>">
                <a href='<?= $space->createUrl('permissions', ['groupId' => $currentGroupId]) ?>'><?= $groupLabel ?></a>
            </li>
            <?php   endforeach; ?>
        </ul>
    <div class="panel-body" style="padding-top:0px;">
        
        <?= PermissionGridEditor::widget(['permissionManager' => $space->permissionManager, 'groupId' => $groupId]); ?>
    </div>
</div>
