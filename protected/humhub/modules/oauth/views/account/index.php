<?php

use humhub\components\View;
use humhub\modules\oauth\widgets\AppsSections;

/* @var View $this */

$sections = AppsSections::widget();
?>
<?php $this->beginContent('@user/views/security/_layout.php') ?>
<?php if (trim($sections) === ''): ?>
    <p class="text-body-secondary"><?= Yii::t('OauthModule.base', 'No apps or tokens have access to your account.') ?></p>
<?php else: ?>
    <?= $sections ?>
<?php endif ?>
<?php $this->endContent() ?>
