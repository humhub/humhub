<?php

use humhub\components\View;
use humhub\modules\oauth\models\Client;

/* @var View $this */
/* @var Client $client */
/* @var string $secret */
/* @var array|string $doneUrl */
?>
<?= $this->render('@oauth/views/common/_credential', [
    'hint' => Yii::t('OauthModule.base', 'Copy the secret now. You won\'t be able to see it again.'),
    'fields' => [
        ['id' => 'oauth-client-id', 'label' => Yii::t('OauthModule.base', 'Client ID'), 'value' => $client->client_id],
        ['id' => 'oauth-client-secret', 'label' => Yii::t('OauthModule.base', 'Client secret'), 'value' => $secret, 'copy' => true],
    ],
    'copied' => Yii::t('OauthModule.base', 'The secret has been copied to your clipboard.'),
    'doneUrl' => $doneUrl,
]) ?>
