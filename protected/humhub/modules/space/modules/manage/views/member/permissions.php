<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\modules\space\modules\manage\widgets\MemberMenu;
use humhub\modules\user\widgets\PermissionGridEditor;
?>
<?= MemberMenu::widget(['space' => $space]); ?>
<br />
<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo Yii::t('SpaceModule.views_admin_members', '<strong>Manage</strong> permissions'); ?>
    </div>
    <div class="panel-body">
        <?php
        echo Yii::t('SpaceModule.views_admin_members', '<strong>Current Group:</strong>');
        echo Html::beginForm($space->createUrl('permissions'), 'GET');
        echo Html::dropDownList('groupId', $groupId, $groups, ['class' => 'form-control', 'onchange' => 'this.form.submit()']);
        echo Html::endForm();
        ?>
        <br />
        <?= PermissionGridEditor::widget(['permissionManager' => $space->permissionManager, 'groupId' => $groupId]); ?>
    </div>
</div>
