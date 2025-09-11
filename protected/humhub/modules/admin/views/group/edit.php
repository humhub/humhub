<?php

use humhub\helpers\Html;
use humhub\modules\space\widgets\SpacePickerField;
use humhub\modules\user\models\forms\EditGroupForm;
use humhub\modules\user\widgets\GroupPicker;
use humhub\modules\user\widgets\UserPickerField;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\form\SortOrderField;
use yii\helpers\Url;

/* @var $isManagerApprovalSetting bool */
/* @var $showDeleteButton bool */
/* @var $group EditGroupForm */
/* @var $canManage bool */
?>

<?php $this->beginContent('@admin/views/group/_manageLayout.php', ['group' => $group]) ?>
<div class="panel-body">
    <?php $form = ActiveForm::begin(['acknowledge' => true]) ?>
    <?= $form->field($group, 'name')->textInput(['readonly' => !$canManage]) ?>
    <?= $form->field($group, 'description')->textarea(['rows' => 5, 'readonly' => !$canManage]) ?>

    <?php if ($canManage) : ?>
        <?= $form->field($group, 'type')->dropDownList($group::getTypeOptions()) ?>
        <?= $form->field($group, 'subgroups')->widget(GroupPicker::class, ['groupType' => $group::TYPE_NORMAL]) ?>
        <?= $form->field($group, 'parent')->widget(GroupPicker::class, ['groupType' => $group::TYPE_SUBGROUP]) ?>
    <?php endif ?>

    <?php if (!$group->is_admin_group): ?>
        <?= SpacePickerField::widget([
            'form' => $form,
            'model' => $group,
            'attribute' => 'defaultSpaceGuid',
            'selection' => $group->defaultSpaces,
            'maxSelection' => 1000,
        ])
        ?>
    <?php endif ?>
    <?php if (!$group->isNewRecord): ?>
        <?= $form->field($group, 'updateSpaceMemberships')->checkbox() ?>
    <?php endif ?>
    <?php if (!$group->is_admin_group): ?>
        <?php $url = ($group->isNewRecord) ? null : Url::to(['/admin/group/admin-user-search', 'id' => $group->id]) ?>
        <?= $form->field($group, 'managerGuids')->widget(UserPickerField::class, [
            'selection' => $group->manager,
            'url' => $url,
            'disabled' => !$canManage,
        ]) ?>
    <?php endif ?>

    <?php if ($canManage) : ?>
        <?= $form->field($group, 'notify_users')->checkbox() ?>

        <?php if (!$group->is_admin_group): ?>
            <?= $form->field($group, 'show_at_registration')->checkbox() ?>
        <?php endif ?>
        <?= $form->field($group, 'show_at_directory')->checkbox() ?>
        <?= $form->field($group, 'sort_order')->widget(SortOrderField::class) ?>
        <?php if (!$group->is_admin_group): ?>
            <?= $form->field($group, 'is_default_group')->checkbox(['disabled' => (bool)$group->is_default_group]) ?>
        <?php endif ?>
    <?php endif ?>

    <?= Button::save()->submit() ?>
    <?php if ($group->canDelete()) : ?>
        <?= Button::danger(Yii::t('AdminModule.user', 'Delete'))
            ->link(['/admin/group/delete', 'id' => $group->id])
            ->options(['data-method' => 'POST'])
            ->confirm(Yii::t('AdminModule.user', 'Are you really sure? Users who are not assigned to another group are automatically assigned to the default group.')) ?>
    <?php endif ?>

    <?php ActiveForm::end() ?>
</div>
<?php $this->endContent() ?>

<?php if ($canManage) : ?>
<script <?= Html::nonce() ?>>
$(document).on('select2:select', '#editgroupform-type', function () {
    const isParentGroup = this.value === '<?= EditGroupForm::TYPE_NORMAL?>';
    document.querySelector('.field-editgroupform-parent').classList.toggle('d-none', isParentGroup);
    document.querySelector('.field-editgroupform-subgroups').classList.toggle('d-none', !isParentGroup);
});
</script>
<?php endif ?>
