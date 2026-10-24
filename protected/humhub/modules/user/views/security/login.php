<?php

use humhub\modules\user\models\Password;
use humhub\modules\user\widgets\security\SecurityLoginSections;

/* @var Password|null $passwordModel */
?>
<?php $this->beginContent('@user/views/security/_layout.php') ?>
<?= SecurityLoginSections::widget(['passwordModel' => $passwordModel]) ?>
<?php $this->endContent() ?>
