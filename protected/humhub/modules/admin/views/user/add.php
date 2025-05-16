<?php

use humhub\compat\HForm;
use humhub\modules\user\services\LinkRegistrationService;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\modal\ModalButton;

/**
 * @var $hForm HForm
 * @var $canInviteByEmail bool
 * @var $canInviteByLink bool
 */
?>

<div class="panel-body">
    <div class="clearfix">
        <div class="float-end">
            <?= Button::back(['index'], Yii::t('AdminModule.base', 'Back to overview'))
                ->right(false) ?>

            <?php if ($canInviteByEmail || $canInviteByLink): ?>
                <?= ModalButton::success(Yii::t('AdminModule.user', 'Invite new people'))
                    ->load(['/user/invite', 'target' => LinkRegistrationService::TARGET_ADMIN])
                    ->icon('invite')
                    ->sm() ?>
            <?php endif; ?>
        </div>

        <h4 class="float-start"><?= Yii::t('AdminModule.user', 'Add new user') ?></h4>
    </div>
    <br>
    <?php $form = ActiveForm::begin(['options' => ['data-ui-widget' => 'ui.form.TabbedForm', 'data-ui-init' => ''], 'acknowledge' => true]); ?>
    <?= $hForm->render($form); ?>
    <?php ActiveForm::end(); ?>
</div>
