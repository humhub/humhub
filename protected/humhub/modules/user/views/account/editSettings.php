<?php

use humhub\modules\user\models\forms\AccountChangeEmail;
use humhub\modules\user\models\forms\AccountChangeUsername;
use humhub\modules\user\models\forms\AccountSettings;
use humhub\modules\user\widgets\account\AccountGeneralSections;

/* @var AccountChangeUsername|null $usernameModel the submitted form, to show its errors */
/* @var AccountChangeEmail|null $emailModel */
/* @var AccountSettings|null $settingsModel */
?>
<?php $this->beginContent('@user/views/account/_userSettingsLayout.php') ?>
<?= AccountGeneralSections::widget([
    'usernameModel' => $usernameModel ?? null,
    'emailModel' => $emailModel ?? null,
    'settingsModel' => $settingsModel ?? null,
]) ?>
<?php $this->endContent() ?>
