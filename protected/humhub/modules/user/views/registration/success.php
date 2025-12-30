<?php

use humhub\widgets\bootstrap\Button;
use humhub\widgets\SiteLogo;
use yii\helpers\Url;

$this->pageTitle = Yii::t('UserModule.auth', 'Create Account');
?>

<div id="user-registration-success" class="container">
    <?= SiteLogo::widget(['place' => SiteLogo::PLACE_LOGIN]) ?>
    <br/>
    <div class="panel panel-default animated fadeIn">
        <div
            class="panel-heading"><?php echo Yii::t('UserModule.auth', '<strong>Your account</strong> has been successfully created!'); ?></div>
        <div class="panel-body">
            <?php if ($needApproval) : ?>
                <p><?php echo Yii::t('UserModule.auth', 'After activating your account by the administrator, you will receive a notification by email.'); ?></p>
                <br/>
                <?= Button::light(Yii::t('UserModule.auth', 'Back'))->link(Url::home())->pjax(false) ?>
            <?php else: ?>
                <p><?php echo Yii::t('UserModule.auth', 'To log in with your new account, click the button below.'); ?></p>
                <br/>
                <a href="<?php echo Url::home() ?>"
                   class="btn btn-primary" data-ui-loader
                   data-pjax-prevent><?php echo Yii::t('UserModule.auth', 'Go to login page') ?></a>
            <?php endif; ?>
        </div>
    </div>
</div>
