<?php

use humhub\modules\ui\view\components\View;
use humhub\modules\user\models\Invite;
use yii\helpers\Url;

/* @var $this View */
/* @var $model Invite */

$this->pageTitle = Yii::t('UserModule.auth', 'Almost there!');
?>

<div class="container" style="text-align: center;">
    <?= humhub\widgets\SiteLogo::widget(['place' => 'login']) ?>
    <br>
    <div class="row">
        <div class="panel panel-default" style="max-width: 300px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading">
                <strong><?= Yii::t('UserModule.auth', 'Almost there!') ?></strong>
            </div>
            <div class="panel-body">
                <p><?= Yii::t('UserModule.auth', 'An email has been sent to {emailAddress}. Please check your inbox to complete the registration.', [
                    'emailAddress' => $model->email,
                ]) ?></p>
                <p><?= Yii::t('UserModule.auth', 'If you don\'t see the email, please check your spam folder.') ?></p>
                <br/>
                <a href="<?= Url::to(["/"]) ?>" data-pjax-prevent data-ui-loader
                   class="btn btn-primary"><?= Yii::t('UserModule.auth', 'back to home') ?></a>
            </div>
        </div>
    </div>
</div>
