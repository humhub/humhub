<?php

use humhub\compat\HForm;
use humhub\widgets\Button;
use humhub\widgets\ModalButton;
use humhub\modules\ui\form\widgets\ActiveForm;

/* @var $hForm HForm */
?>

<div class="panel-body">
    <div class="clearfix">

        <div class="pull-right">
            <?= Button::back(['index'], Yii::t('AdminModule.base', 'Back to overview'))
                ->right(false) ?>

            <?= ModalButton::success(Yii::t('AdminModule.user', 'Send invite'))
                ->load(['/user/invite'])->icon('invite')->sm() ?>
        </div>

        <h4 class="pull-left"><?= Yii::t('AdminModule.user', 'Add new user') ?></h4>
    </div>
    <br>
    <?php $form = ActiveForm::begin(['options' => ['data-ui-widget' => 'ui.form.TabbedForm', 'data-ui-init' => ''], 'acknowledge' => true]); ?>
        <?= $hForm->render($form); ?>
    <?php ActiveForm::end(); ?>
</div>
