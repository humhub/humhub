<?php

use yii\helpers\Url;
use humhub\widgets\bootstrap\Button;

?>

<?= Button::primary(Yii::t("UserModule.profile", "Edit account"))->link(Url::toRoute('/user/account/edit'))->cssClass('edit-account') ?>
