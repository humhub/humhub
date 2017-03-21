<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use humhub\modules\space\modules\manage\widgets\MemberMenu;
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('SpaceModule.views_admin_members', '<strong>Manage</strong> members'); ?>
    </div>
    <?= MemberMenu::widget(['space' => $space]); ?>
    <div class="panel-body">

        <p><?= Yii::t('SpaceModule.manage', 'As owner of this space you can transfer this role to another administrator in space.'); ?></p>

        <?php $form = ActiveForm::begin([]); ?>
        <?= $form->field($model, 'ownerId')->dropDownList($model->getNewOwnerArray()); ?>

        <hr>
        <?= Html::submitButton(Yii::t('SpaceModule.manage', 'Transfer ownership'), ['class' => 'btn btn-danger', 'data-confirm' => 'Are you really sure?']); ?>

        <?php ActiveForm::end(); ?>

    </div>
</div>





