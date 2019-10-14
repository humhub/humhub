<?php

use yii\helpers\Url;

$this->pageTitle = Yii::t('UserModule.views_auth_recoverPassword', 'Password recovery');
?>

<a class="brand" href="/dashboard"><img src="<?= $this->theme->getBaseUrl(); ?>/img/slogan_black.png"></a>

<div class="content">

    <div class="create-account-content col-md-4">

        <h1><?= Yii::t('UserModule.views_auth_recoverPassword_success', 'Recovery link sent!'); ?></h1>
        <h5><?= Yii::t('UserModule.views_auth_recoverPassword_success', 'We’ve sent you an email containing a link that will allow you to reset your password.'); ?></h5>

        <a href="<?php echo Url::home() ?>" data-ui-loader class="btn btn-primary"><?php echo Yii::t('UserModule.views_auth_recoverPassword_success', 'back to home') ?></a>

    </div>

</div>
