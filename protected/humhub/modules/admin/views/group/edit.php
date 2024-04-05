<?php

use humhub\modules\space\widgets\SpacePickerField;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\ui\form\widgets\SortOrderField;
use humhub\modules\user\models\forms\EditGroupForm;
use humhub\modules\user\widgets\UserPickerField;
use humhub\widgets\Button;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $isManagerApprovalSetting bool */
/* @var $showDeleteButton bool */
/* @var $group EditGroupForm */

?>

<?php $this->beginContent('@admin/views/group/_manageLayout.php', ['group' => $group]) ?>
<div class="panel-body">
    <?php $form = ActiveForm::begin(['acknowledge' => true]); ?>
    <?= $form->field($group, 'name'); ?>
    <?= $form->field($group, 'description')->textarea(['rows' => 5]); ?>

    <?php if (!$group->is_admin_group): ?>
        <?= SpacePickerField::widget([
            'form' => $form,
            'model' => $group,
            'attribute' => 'defaultSpaceGuid',
            'selection' => $group->defaultSpaces,
            'maxSelection' => 1000,
        ])
        ?>
    <?php endif; ?>
    <?php if (!$group->isNewRecord): ?>
        <?= $form->field($group, 'updateSpaceMemberships')->checkbox(); ?>
    <?php endif; ?>
    <?php if (!$group->is_admin_group): ?>
        <?php $url = ($group->isNewRecord) ? null : Url::to(['/admin/group/admin-user-search', 'id' => $group->id]); ?>
        <?= $form->field($group, 'managerGuids')->widget(UserPickerField::class, ['selection' => $group->manager, 'url' => $url]); ?>
    <?php endif; ?>

    <?= $form->field($group, 'notify_users')->checkbox(); ?>

    <?php if (!$group->is_admin_group): ?>
        <?= $form->field($group, 'show_at_registration')->checkbox(); ?>
    <?php endif; ?>
    <?= $form->field($group, 'show_at_directory')->checkbox(); ?>
    <?= $form->field($group, 'sort_order')->widget(SortOrderField::class) ?>
    <?php if (!$group->is_admin_group): ?>
        <?= $form->field($group, 'is_default_group')->checkbox(['disabled' => (bool)$group->is_default_group]); ?>
    <?php endif; ?>

    <?= Button::save()->submit(); ?>
    <?php
    if ($group->canDelete()) {
        echo Html::a(Yii::t('AdminModule.user', 'Delete'), Url::toRoute(['/admin/group/delete', 'id' => $group->id]), [
            'class' => 'btn btn-danger',
            'data-method' => 'POST',
            'data-confirm' => Yii::t('AdminModule.user', 'Are you really sure? Users who are not assigned to another group are automatically assigned to the default group.'),
        ]);
    }
    ?>
    <?php ActiveForm::end(); ?>
</div>
<?php $this->endContent(); ?>
