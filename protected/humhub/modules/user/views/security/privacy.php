<?php

use humhub\modules\user\models\forms\PrivacySettings;
use humhub\modules\user\widgets\security\SecurityPrivacySections;

/* @var PrivacySettings $privacyModel */
?>
<?php $this->beginContent('@user/views/security/_layout.php') ?>
<?= SecurityPrivacySections::widget(['privacyModel' => $privacyModel]) ?>
<?php $this->endContent() ?>
