<?php

use humhub\components\View;
use humhub\modules\user\models\Invite;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\SiteLogo;
use yii\helpers\Url;

/* @var $this View */
/* @var $model Invite */

$this->pageTitle = Yii::t('UserModule.auth', 'Almost there!');
?>

<div id="user-auth-register-success" class="container">
    <?= SiteLogo::widget(['place' => SiteLogo::PLACE_LOGIN]) ?>
    <br>
    <div class="panel panel-default">
        <div class="panel-heading">
            <strong><?= Yii::t('UserModule.auth', 'Almost there!') ?></strong>
        </div>
        <div class="panel-body">
            <p><?= Yii::t('UserModule.auth', 'An email has been sent to {emailAddress}. Please check your inbox to complete the registration.', [
                'emailAddress' => $model->email,
            ]) ?></p>
            <p><?= Yii::t('UserModule.auth', 'If you don\'t see the email, please check your spam folder.') ?></p>
            <br/>
            <?= Button::light(Yii::t('UserModule.auth', 'Back'))->link(Url::home())->pjax(false) ?>
        </div>
    </div>
</div>
