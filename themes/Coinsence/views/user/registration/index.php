<?php

use yii\helpers\Html;

$this->pageTitle = Yii::t('UserModule.views_auth_createAccount', 'Create Account');
?>

<a class="brand" href="/dashboard"><img src="http://coinsence.localhost/uploads/logo_image/logo.png?cacheId=0"></a>

<div class="content">

    <div class="create-account-content col-md-4" id="create-account-form">

        <h1><?= Yii::t('UserModule.views_auth_createAccount', 'Welcome!'); ?></h1>
        <h5><?= Yii::t('UserModule.views_auth_createAccount', 'Create an account by entering your details below.'); ?></h5>

        <?php $form = \yii\bootstrap\ActiveForm::begin([ 'id' => 'registration-form', 'enableClientValidation' => false ]); ?>
        <?= $hForm->render($form); ?>
        <?php \yii\bootstrap\ActiveForm::end(); ?>

    </div>

</div>
