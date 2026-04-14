<?php

use humhub\components\View;
use humhub\modules\user\models\Invite;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var $this View */
/* @var $model Invite */

$this->pageTitle = Yii::t('UserModule.auth', 'Almost there!');
?>

<?php Modal::beginDialog([
    'id' => 'user-auth-register-success-modal',
    'title' => Yii::t('UserModule.auth', '<strong>Registration</strong> successful!'),
    'footer' => ModalButton::cancel(Yii::t('base', 'Close')),
]) ?>

    <div class="text-center">
        <p><?= Yii::t('UserModule.auth', 'An email has been sent to {emailAddress}. Please check your inbox to complete the registration.', [
            'emailAddress' => $model->email,
        ]) ?></p>
        <p><?= Yii::t('UserModule.auth', 'If you don\'t see the email, please check your spam folder.') ?></p>
    </div>

<?php Modal::endDialog() ?>
