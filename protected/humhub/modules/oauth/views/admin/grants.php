<?php

use humhub\components\View;
use humhub\modules\oauth\widgets\AdminGrantTable;

/* @var View $this */
?>
<?php $this->beginContent('@admin/views/authentication/_authenticationLayout.php') ?>
<?= $this->render('_tabs') ?>
<p class="mt-3">
    <?= Yii::t('OauthModule.base', 'Connected apps, devices and personal access tokens of all users.') ?>
</p>
<?= AdminGrantTable::widget() ?>
<?php $this->endContent() ?>
