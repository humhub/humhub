<?php

use humhub\components\View;
use humhub\helpers\Html;

/* @var View $this */
/* @var string $token */
/* @var string $name */
?>
<?php $this->beginContent('@user/views/security/_layout.php') ?>
<section class="security-section mb-4">
    <h3><?= Yii::t('OauthModule.base', 'Token “{name}” created', ['name' => Html::encode($name)]) ?></h3>
    <?= $this->render('@oauth/views/common/_credential', [
        'hint' => Yii::t('OauthModule.base', 'Copy the token now. You won\'t be able to see it again.'),
        'fields' => [
            ['id' => 'oauth-created-token', 'label' => Yii::t('OauthModule.base', 'Personal access token'), 'value' => $token, 'copy' => true],
        ],
        'copied' => Yii::t('OauthModule.base', 'The token has been copied to your clipboard.'),
        'doneUrl' => ['/user/security/sessions'],
    ]) ?>
</section>
<?php $this->endContent() ?>
