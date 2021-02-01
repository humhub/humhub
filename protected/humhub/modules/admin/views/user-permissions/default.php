<?php

use humhub\modules\user\widgets\PermisionGridModuleFilter;
use humhub\modules\user\widgets\PermissionGridEditor;
use yii\helpers\Url;

/* @var $defaultPermissionManager \humhub\modules\content\components\ContentContainerDefaultPermissionManager */
/* @var $groups array */
/* @var $groupId string */

?>
<div class="panel-body">
    <h4><?= Yii::t('AdminModule.user', 'Default User Permissions'); ?></h4>
    <div class="help-block">
        <?= Yii::t('AdminModule.user', 'Here you can define default permissions for user account per different user-types. These settings overwrite default permissions from config file and can be overwritten for each individual account security settings.'); ?>
        <br><br>
        <?= Yii::t('AdminModule.user', 'Permissions are assigned to different user-types. To edit a default permission, select the user-types you want to edit and change the drop-down value of the given permission.'); ?>
    </div>

    <div class="clearfix">
        <?= PermisionGridModuleFilter::widget() ?>
    </div>

    <ul id="tabs" class="nav nav-tabs tab-sub-menu permission-group-tabs">
        <?php foreach ($groups as $currentGroupId => $groupLabel) : ?>
            <li class="<?= ($groupId === $currentGroupId) ? 'active' : '' ?>">
                <a href="<?= Url::toRoute(['/admin/user-permissions', 'groupId' => $currentGroupId]) ?>"><?= $groupLabel ?></a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="panel-body" style="padding-top: 0px;">
        <?= PermissionGridEditor::widget(['permissionManager' => $defaultPermissionManager, 'groupId' => $groupId]); ?>
    </div>
</div>