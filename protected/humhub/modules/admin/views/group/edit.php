<?php

use yii\widgets\ActiveForm;
use humhub\compat\CHtml;
use humhub\modules\user\widgets\PermissionGridEditor;
use yii\helpers\Url;
use yii\helpers\Html;

?>
<div class="panel panel-default">
    <?php if (!$group->isNewRecord) : ?>
        <div class="panel-heading">
                <?php echo Yii::t('AdminModule.views_group_edit', '<strong>Edit</strong> group {groupName}', ['groupName' => $group->name]); ?>
        </div>
    <?php else: ?>
        <div class="panel-heading"><?php echo Yii::t('AdminModule.views_group_edit', '<strong>Create</strong> new group'); ?></div>
    <?php endif; ?>
        
    <div class="panel-body">
        <?php if (!$group->isNewRecord) : ?>
            <?= \humhub\modules\admin\widgets\GroupManagerMenu::widget(); ?>
            <br />
        <?php endif; ?>
        
        <?php $form = ActiveForm::begin(); ?>
            <?php echo $form->field($group, 'name'); ?>
            <?php echo $form->field($group, 'description')->textarea(['rows' => 5]); ?>
            <?php echo $form->field($group, 'defaultSpaceGuid')->textInput(['id' => 'space_select']); ?>
            <?php
            echo \humhub\modules\space\widgets\Picker::widget([
                'inputId' => 'space_select',
                'maxSpaces' => 1,
                'model' => $group,
                'attribute' => 'defaultSpaceGuid'
            ]);
            ?>
            <?php if($isManagerApprovalSetting): ?>
                <?php echo $form->field($group, 'managerGuids', ['inputOptions' => ['id' => 'user_select']]); ?>
                <?php
                $url = ($group->isNewRecord) ? null : Url::toRoute('/admin/group/admin-user-search');
                echo \humhub\modules\user\widgets\UserPicker::widget([
                    'inputId' => 'user_select',
                    'model' => $group,
                    'attribute' => 'managerGuids',
                    'userSearchUrl' => $url,
                    'data' => ['id' => $group->id],
                    'placeholderText' => 'Add a user'
                ]);
                ?>
            <?php endif; ?>

            <strong><?php echo Yii::t('AdminModule.views_group_edit', 'Visibility'); ?></strong>
            <br>
            <br>
            <?php if($isManagerApprovalSetting): ?>
                <?php echo $form->field($group, 'show_at_registration')->checkbox(); ?>
            <?php endif; ?>
            <?php echo $form->field($group, 'show_at_directory')->checkbox(); ?>

            <?php echo CHtml::submitButton(Yii::t('AdminModule.views_group_edit', 'Save'), array('class' => 'btn btn-primary')); ?>  

            <?php
            if ($showDeleteButton) {
                echo Html::a(Yii::t('AdminModule.views_group_edit', 'Delete'), Url::toRoute(['/admin/group/delete', 'id' => $group->id]), array('class' => 'btn btn-danger', 'data-method' => 'POST'));
            }?>
        <?php ActiveForm::end(); ?>
    </div>
</div>