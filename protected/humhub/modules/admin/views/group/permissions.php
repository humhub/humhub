<?php

use humhub\modules\user\widgets\PermissionGridEditor;

?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo Yii::t('AdminModule.views_group_permissions', '<strong>Edit</strong> group {groupName}', ['groupName' => $group->name]); ?>
    </div>
    <?= \humhub\modules\admin\widgets\GroupManagerMenu::widget(); ?>
    <div class="panel-body">
        <?= PermissionGridEditor::widget(['permissionManager' => Yii::$app->user->permissionManager, 'groupId' => $group->id]); ?>
    </div>
</div>