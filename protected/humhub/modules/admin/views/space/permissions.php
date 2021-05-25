<?php

use humhub\modules\user\widgets\PermisionGridModuleFilter;
use humhub\modules\user\widgets\PermissionGridEditor;
use yii\helpers\Url;

/* @var $defaultPermissionManager \humhub\modules\content\components\ContentContainerDefaultPermissionManager */
/* @var $groups array */
/* @var $groupId string */

?>
<h4><?= Yii::t('AdminModule.space', 'Default Space Permissions'); ?></h4>
<div class="help-block">
    <?= Yii::t('AdminModule.space', 'These options allow you to set the default permissions for all Spaces. Authorized users are able individualize these for each Space. Further entries are added with the installation of new modules.'); ?>
    <br><br>
    <?= Yii::t('AdminModule.space', 'By using user roles, you can create different permission groups within a Space. These can also be individualized by authorized users for each and every Space and are only relevant for that specific Space.'); ?>
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