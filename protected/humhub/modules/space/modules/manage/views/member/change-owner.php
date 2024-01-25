<?php

use humhub\modules\space\modules\manage\models\ChangeOwnerForm;
use yii\helpers\Html;
use humhub\modules\space\modules\manage\widgets\MemberMenu;
use yii\widgets\ActiveForm;
use humhub\widgets\Button;

/* @var $model ChangeOwnerForm */
?>

<div class="card card-default">
    <div class="card-header">
        <?= Yii::t('SpaceModule.manage', '<strong>Manage</strong> members'); ?>
    </div>

    <?= MemberMenu::widget(['space' => $space]); ?>

    <div class="card-body">
        <p><?= Yii::t('SpaceModule.manage', 'As owner of this space you can transfer this role to another administrator in space.'); ?></p>

        <?php $form = ActiveForm::begin([]); ?>
            <?= $form->field($model, 'ownerId')->dropDownList($model->getNewOwnerArray()) ?>
            <hr>
            <?= Button::danger(Yii::t('SpaceModule.manage', 'Transfer ownership'))->action('client.submit')->confirm() ?>
        <?php ActiveForm::end(); ?>
    </div>
</div>
