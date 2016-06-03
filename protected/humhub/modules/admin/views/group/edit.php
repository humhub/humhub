<?php

use yii\widgets\ActiveForm;
use humhub\compat\CHtml;
use yii\helpers\Url;
use yii\helpers\Html;
?>

<?php $this->beginContent('@admin/views/group/_manageLayout.php', ['group' => $group]) ?>
<div class="panel-body">
     <?php $form = ActiveForm::begin(); ?>
    <?php echo $form->field($group, 'name'); ?>
    <?php echo $form->field($group, 'description')->textarea(['rows' => 5]); ?>

    <?php if (!$group->is_admin_group): ?>
        <?php echo $form->field($group, 'defaultSpaceGuid')->textInput(['id' => 'space_select']); ?>
        <?php
        echo \humhub\modules\space\widgets\Picker::widget([
            'inputId' => 'space_select',
            'maxSpaces' => 1,
            'model' => $group,
            'attribute' => 'defaultSpaceGuid'
        ]);
        ?>
    <?php endif; ?>

    <?php if ($isManagerApprovalSetting && !$group->is_admin_group): ?>
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
    <?php if (!$group->is_admin_group): ?>
        <?php echo $form->field($group, 'show_at_registration')->checkbox(); ?>
    <?php endif; ?>
    <?php echo $form->field($group, 'show_at_directory')->checkbox(); ?>

    <?php echo CHtml::submitButton(Yii::t('AdminModule.views_group_edit', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => "")); ?>  

    <?php
    if ($showDeleteButton) {
        echo Html::a(Yii::t('AdminModule.views_group_edit', 'Delete'), Url::toRoute(['/admin/group/delete', 'id' => $group->id]), array('class' => 'btn btn-danger', 'data-method' => 'POST'));
    }
    ?>
    <?php ActiveForm::end(); ?>
</div>
<?php $this->endContent(); ?>