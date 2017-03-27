<?php

use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;
use humhub\compat\CHtml;
use humhub\modules\user\widgets\UserPickerField;
use humhub\modules\space\widgets\SpacePickerField;
?>

<?php $this->beginContent('@admin/views/group/_manageLayout.php', ['group' => $group]) ?>
<div class="panel-body">
    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($group, 'name'); ?>
    <?= $form->field($group, 'description')->textarea(['rows' => 5]); ?>

    <?php if (!$group->is_admin_group): ?>
        <?= SpacePickerField::widget([
            'form' => $form,
            'model' => $group,
            'attribute' => 'defaultSpaceGuid',
            'selection' => $group->defaultSpace,
            'maxSelection' => 1,
        ])
        ?>
    <?php endif; ?>

    <?php if ($isManagerApprovalSetting && !$group->is_admin_group): ?>
        <?php $url = ($group->isNewRecord) ? null : Url::to(['/admin/group/admin-user-search', 'id' => $group->id]); ?>
        <?=  UserPickerField::widget([
            'form' => $form,
            'model' => $group,
            'attribute' => 'managerGuids',
            'selection' => $group->manager,
            'url' => $url
        ])
        ?>
    <?php endif; ?>

    <strong><?= Yii::t('AdminModule.views_group_edit', 'Visibility'); ?></strong>
    <br>
    <br>
    <?php if (!$group->is_admin_group): ?>
        <?= $form->field($group, 'show_at_registration')->checkbox(); ?>
    <?php endif; ?>
    <?= $form->field($group, 'show_at_directory')->checkbox(); ?>

    <?= CHtml::submitButton(Yii::t('AdminModule.views_group_edit', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

    <?php
    if ($showDeleteButton) {
        echo Html::a(Yii::t('AdminModule.views_group_edit', 'Delete'), Url::toRoute(['/admin/group/delete', 'id' => $group->id]), ['class' => 'btn btn-danger', 'data-method' => 'POST']);
    }
    ?>
<?php ActiveForm::end(); ?>
</div>
<?php $this->endContent(); ?>