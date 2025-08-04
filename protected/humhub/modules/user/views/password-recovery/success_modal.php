<?php

use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;
use yii\helpers\Url;

?>

<?php Modal::beginDialog([
    'title' => Yii::t('UserModule.auth', '<strong>Password</strong> recovery'),
    'footer' => ModalButton::primary(Yii::t('UserModule.auth', 'back to home'))->link(Url::home())->pjax(false),
]) ?>
    <p><?= Yii::t('UserModule.auth', 'If a user account associated with this email address exists, further instructions will be sent to you by email shortly.') ?></p>
<?php Modal::endDialog() ?>
