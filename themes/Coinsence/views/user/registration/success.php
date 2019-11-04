<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->pageTitle = Yii::t('UserModule.views_auth_createAccount', 'Create Account');
?>

<a class="brand" href="/dashboard"><img src="<?= $this->theme->getBaseUrl(); ?>/img/slogan_black.png"></a>

<div class="content">

    <div class="create-account-content col-md-8">

        <h1><?= Yii::t('UserModule.views_auth_createAccount_success', 'Your account has been successfully created!'); ?></h1>

        <?php if ($needApproval) : ?>
            <h5><?= Yii::t('UserModule.views_auth_createAccount_success', 'After activating your account by the administrator, you will receive a notification by email.'); ?></h5>
            <br/>
            <a href="<?php echo Url::home() ?>" class="btn btn-primary" data-ui-loader data-pjax-prevent><?php echo Yii::t('UserModule.views_auth_createAccount_success', 'back to home') ?></a>
        <?php else: ?>
            <h5><?= Yii::t('UserModule.views_auth_createAccount_success', 'To log in with your new account, click the button below.'); ?></h5>
            <br/>
            <a href="<?php echo Url::home() ?>"
               class="btn btn-primary" data-ui-loader data-pjax-prevent><?php echo Yii::t('UserModule.views_auth_createAccount_success', 'Go to login page') ?></a>
        <?php endif; ?>

    </div>

</div>
