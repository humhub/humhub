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
    <?= $this->render('@oauth/views/common/_credential', [
        'hint' => Yii::t('OauthModule.base', 'Copy the secret now. You won\'t be able to see it again.'),
        'fields' => [
            ['id' => 'oauth-client-id', 'label' => Yii::t('OauthModule.base', 'Client ID'), 'value' => $client->client_id],
            ['id' => 'oauth-client-secret', 'label' => Yii::t('OauthModule.base', 'Client secret'), 'value' => $secret, 'copy' => true],
        ],
        'copied' => Yii::t('OauthModule.base', 'The secret has been copied to your clipboard.'),
        'doneUrl' => ['/oauth/admin/clients'],
    ]) ?>
</div>
<?php $this->endContent() ?>
