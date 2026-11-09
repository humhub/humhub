<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\user\models\forms\SudoConfirmForm;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\modal\ModalButton;
use yii\helpers\Url;

/* @var View $this */
/* @var SudoConfirmForm $model */
/* @var string $returnUrl */
/* @var bool $inModal */

// On the page, Cancel leads home: the return URL is the protected page that sent the user here
?>
<?php if ($model->hasPasswordFactor()) : ?>
    <p><?= Yii::t('UserModule.auth', "To continue, confirm it's you.") ?></p>

    <?php $form = ActiveForm::begin(['id' => 'sudo-confirm-form', 'action' => ['/user/sudo/confirm', 'returnUrl' => $returnUrl]]) ?>
        <?= $form->field($model, 'password')->passwordInput(['autofocus' => true, 'autocomplete' => 'current-password', 'maxlength' => 255]) ?>

        <div class="d-flex justify-content-end gap-2">
            <?php if ($inModal) : ?>
                <?= ModalButton::cancel() ?>
                <?= ModalButton::primary(Yii::t('UserModule.auth', 'Confirm'))->submit()->options(['data-action-data-type' => 'json']) ?>
            <?php else : ?>
                <?= Button::light(Yii::t('base', 'Cancel'))->link(Url::home()) ?>
                <?= Button::primary(Yii::t('UserModule.auth', 'Confirm'))->submit() ?>
            <?php endif; ?>
        </div>
    <?php ActiveForm::end() ?>
<?php else : ?>
    <p><?= Yii::t('UserModule.auth', 'To continue, sign in again.') ?></p>

    <?= Html::beginForm(['/user/sudo/relogin', 'returnUrl' => $returnUrl], 'post', ['id' => 'sudo-relogin-form']) ?>
        <div class="d-flex justify-content-end gap-2">
            <?= $inModal ? ModalButton::cancel() : Button::light(Yii::t('base', 'Cancel'))->link(Url::home()) ?>
            <?= Button::primary(Yii::t('UserModule.auth', 'Sign in again'))->submit() ?>
        </div>
    <?= Html::endForm() ?>
<?php endif; ?>
