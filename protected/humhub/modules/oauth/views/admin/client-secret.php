<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\oauth\models\Client;

/* @var View $this */
/* @var Client $client */
/* @var string $secret */
?>
<?php $this->beginContent('@admin/views/authentication/_authenticationLayout.php') ?>
<?= $this->render('_tabs') ?>
<div class="panel-body">
    <h4><?= Yii::t('OauthModule.base', 'Secret of “{name}”', ['name' => Html::encode($client->name)]) ?></h4>
    <?= $this->render('@oauth/views/common/_client-secret', ['client' => $client, 'secret' => $secret, 'doneUrl' => ['/oauth/admin/clients']]) ?>
</div>
<?php $this->endContent() ?>
