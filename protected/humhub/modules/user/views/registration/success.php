<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->pageTitle = Yii::t('UserModule.auth', 'Create Account');
?>

<div class="container" style="text-align: center;">
    <h1 id="app-title" class="animated fadeIn"><?php echo Html::encode(Yii::$app->name); ?></h1>
    <br/>
    <div class="row">
        <div class="panel panel-default animated fadeIn" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">
            <div
                class="panel-heading"><?php echo Yii::t('UserModule.auth', '<strong>Your account</strong> has been successfully created!'); ?></div>
            <div class="panel-body">
                <?php if ($needApproval) : ?>
                    <p><?php echo Yii::t('UserModule.auth', 'After activating your account by the administrator, you will receive a notification by email.'); ?></p>
                    <br/>
                    <a href="<?php echo Url::home() ?>" class="btn btn-primary" data-ui-loader data-pjax-prevent><?php echo Yii::t('UserModule.auth', 'back to home') ?></a>
                <?php else: ?>
                    <p><?php echo Yii::t('UserModule.auth', 'To log in with your new account, click the button below.'); ?></p>
                    <br/>
                    <a href="<?php echo Url::home() ?>"
                       class="btn btn-primary" data-ui-loader data-pjax-prevent><?php echo Yii::t('UserModule.auth', 'Go to login page') ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>



