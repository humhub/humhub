<?php

use humhub\libs\Html;

/**
 * @var string $newEmail
 */

?>
<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
<?= Yii::t('UserModule.account', 'Your e-mail address has been successfully changed to {email}.', ['{email}' => Html::encode($newEmail)]); ?>
<?php $this->endContent(); ?>
