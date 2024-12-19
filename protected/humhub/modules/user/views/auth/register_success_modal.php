<?php

use humhub\widgets\modal\Modal;
use yii\helpers\Url;

$this->pageTitle = Yii::t('UserModule.auth', 'Registration successful');
?>

<?php Modal::beginDialog([
    'title' => Yii::t('UserModule.auth', '<strong>Registration</strong> successful!'),
]) ?>

    <div class="text-center">
        <p><?php echo Yii::t('UserModule.auth', 'Please check your email and follow the instructions!'); ?></p>
        <br>
        <a href="<?= Url::to(["/"]) ?>" data-pjax-prevent data-ui-loader
           class="btn btn-primary"><?php echo Yii::t('UserModule.auth', 'back to home') ?></a>
    </div>

<?php Modal::endDialog() ?>
