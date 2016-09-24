<?php

use yii\helpers\Html;
use humhub\modules\space\modules\manage\widgets\MemberMenu;
use yii\widgets\ActiveForm;
?>


<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo Yii::t('SpaceModule.views_admin_members', '<strong>Manage</strong> members'); ?>
    </div>
    <?= MemberMenu::widget(['space' => $space]); ?>
    <div class="panel-body">

        <p>
            <?php echo Yii::t('SpaceModule.manage', 'As owner of this space you can transfer this role to another administrator in space.'); ?>
        </p>

        <?php
        $form = ActiveForm::begin([])
        ?>
        <?= $form->field($model, 'ownerId')->dropDownList($model->getNewOwnerArray()) ?>

        <hr />
        <?= Html::submitButton(Yii::t('SpaceModule.manage', 'Transfer ownership'), ['class' => 'btn btn-danger', 'data-confirm' => 'Are you really sure?']) ?>

        <?php ActiveForm::end() ?>

    </div>
</div>





