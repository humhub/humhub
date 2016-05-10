<?php

use yii\helpers\Html;
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
