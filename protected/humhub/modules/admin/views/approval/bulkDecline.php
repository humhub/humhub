<?php

use humhub\libs\Html;
use humhub\modules\admin\controllers\ApprovalController;
use humhub\modules\admin\models\forms\ApproveUserForm;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\user\models\User;
use humhub\widgets\Button;
use yii\helpers\Url;

/* @var $users User[] */
/* @var $approveFormModel ApproveUserForm */
?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.user', 'Decline and delete the following registrations:') ?></h4>
    <ul>
        <?php foreach ($users as $user): ?>
            <li><strong><?= Html::encode($user->displayName) ?></strong></li>
        <?php endforeach; ?>
    </ul>
    <br>

    <?php $form = ActiveForm::begin(); ?>

    <?php foreach ($users as $user): ?>
        <?= Html::hiddenInput('ids[]', $user->id) ?>
    <?php endforeach; ?>
    <?= Html::hiddenInput('action', ApprovalController::ACTION_DELINE) ?>
    <?= $form->field($approveFormModel, 'confirm')->checkbox(); ?>

    <hr>
    <?= Button::save(Yii::t('AdminModule.user', 'Send & decline'))->submit() ?>
    <?= Button::primary(Yii::t('AdminModule.user', 'Cancel'))->link(Url::to(['index'])) ?>

    <?php ActiveForm::end(); ?>
</div>
