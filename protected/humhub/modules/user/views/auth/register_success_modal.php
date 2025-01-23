<?php

use humhub\modules\ui\view\components\View;
use humhub\modules\user\models\Invite;
use humhub\widgets\ModalButton;

/* @var $this View */
/* @var $model Invite */

$this->pageTitle = Yii::t('UserModule.auth', 'Registration successful');
?>

<div class="modal-dialog modal-dialog-small animated fadeIn">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">
                <strong><?= Yii::t('UserModule.auth', 'Almost there!') ?></strong>
            </h4>
        </div>
        <div class="modal-body text-center">
            <p><?= Yii::t('UserModule.auth', 'An email has been sent to {emailAddress}. Please check your inbox to complete the registration.', [
                'emailAddress' => $model->email,
            ]) ?></p>
            <p><?= Yii::t('UserModule.auth', 'If you don\'t see the email, please check your spam folder.') ?></p>
        </div>
        <div class="modal-footer">
            <?= ModalButton::cancel(Yii::t('base', 'Close')) ?>
        </div>
    </div>
</div>
