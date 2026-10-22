<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\oauth\models\Client;

/* @var View $this */
/* @var Client $client */
/* @var string $secret */
?>
<?php $this->beginContent('@user/views/security/_layout.php') ?>
<section class="security-section mb-4">
    <h2 class="h5 mb-1"><?= Yii::t('OauthModule.base', 'Secret of “{name}”', ['name' => Html::encode($client->name)]) ?></h2>
    <?= $this->render('@oauth/views/common/_client-secret', ['client' => $client, 'secret' => $secret, 'doneUrl' => ['/oauth/account/index']]) ?>
</section>
<?php $this->endContent() ?>
