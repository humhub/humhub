<?php

use humhub\modules\user\widgets\PermisionGridModuleFilter;
use humhub\modules\user\widgets\PermissionGridEditor;
use yii\helpers\Url;

/* @var $defaultPermissionManager \humhub\modules\content\components\ContentContainerDefaultPermissionManager */
/* @var $groups array */
/* @var $groupId int */

?>
<h4><?= Yii::t('AdminModule.space', 'Default Space Permissions'); ?></h4>
<div class="help-block">
    <?= Yii::t('AdminModule.space', 'Here you can define default permissions for new spaces. These settings overwrite default permissions from config file and can be overwritten for each individual space.'); ?>
    <br><br>
    <?= Yii::t('AdminModule.space', 'Permissions are assigned to different user-roles. To edit a default permission, select the user-role you want to edit and change the drop-down value of the given permission.'); ?>
</div>

<div class="clearfix">
    <?= PermisionGridModuleFilter::widget() ?>
</div>

<ul id="tabs" class="nav nav-tabs tab-sub-menu permission-group-tabs">
    <?php foreach ($groups as $currentGroupId => $groupLabel) : ?>
        <li class="<?= ($groupId === $currentGroupId) ? 'active' : '' ?>">
            <a href="<?= Url::toRoute(['/admin/space/permissions', 'groupId' => $currentGroupId]) ?>"><?= $groupLabel ?></a>
        </li>
    <?php endforeach; ?>
</ul>

<div class="panel-body" style="padding-top: 0px;">
    <?= PermissionGridEditor::widget(['permissionManager' => $defaultPermissionManager, 'groupId' => $groupId]); ?>
</div>