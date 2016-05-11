<?php

use humhub\modules\user\widgets\PermissionGridEditor;
?>
<?php $this->beginContent('@admin/views/group/_manageLayout.php', ['group' => $group]) ?>

<?= PermissionGridEditor::widget(['permissionManager' => Yii::$app->user->permissionManager, 'groupId' => $group->id]); ?>

<?php $this->endContent(); ?>