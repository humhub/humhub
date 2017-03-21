<?php

use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;
use humhub\compat\CHtml;
?>

<?php $this->beginContent('@admin/views/group/_manageLayout.php', ['group' => $group]); ?>
<div class="panel-body">
    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($group, 'name'); ?>
    <?= $form->field($group, 'description')->textarea(['rows' => 5]); ?>

    <?php if (!$group->is_admin_group): ?>
        <?= $form->field($group, 'defaultSpaceGuid')->textInput(['id' => 'space_select']); ?>
        <?= \humhub\modules\space\widgets\Picker::widget([
            'inputId' => 'space_select',
            'maxSpaces' => 1,
            'model' => $group,
            'attribute' => 'defaultSpaceGuid'
        ]);
        ?>
    <?php endif; ?>

    <?php if ($isManagerApprovalSetting && !$group->is_admin_group): ?>
        <?= $form->field($group, 'managerGuids', ['inputOptions' => ['id' => 'user_select']]); ?>
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

    <strong><?= Yii::t('AdminModule.views_group_edit', 'Visibility'); ?></strong>
    <br>
    <br>
    <?php if (!$group->is_admin_group): ?>
        <?= $form->field($group, 'show_at_registration')->checkbox(); ?>
    <?php endif; ?>
    <?= $form->field($group, 'show_at_directory')->checkbox(); ?>

    <?= CHtml::submitButton(Yii::t('AdminModule.views_group_edit', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => "")); ?>  

    <?php
    if ($showDeleteButton) {
        echo Html::a(Yii::t('AdminModule.views_group_edit', 'Delete'), Url::toRoute(['/admin/group/delete', 'id' => $group->id]), array('class' => 'btn btn-danger', 'data-method' => 'POST'));
    }
    ?>
    <?php ActiveForm::end(); ?>
</div>
<?php $this->endContent(); ?>