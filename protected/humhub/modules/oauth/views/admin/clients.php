<?php

use humhub\components\View;
use humhub\modules\oauth\widgets\AdminClientTable;

/* @var View $this */
?>
<?php $this->beginContent('@admin/views/authentication/_authenticationLayout.php') ?>
<?= $this->render('_tabs') ?>
<p class="mt-3">
    <?= Yii::t('OauthModule.base', 'Applications that may ask users for access. Deactivating or deleting a client ends the access of all its grants.') ?>
</p>
<?= AdminClientTable::widget() ?>
<?php $this->endContent() ?>
