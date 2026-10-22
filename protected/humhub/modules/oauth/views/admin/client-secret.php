<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\oauth\models\Client;
use humhub\widgets\bootstrap\Button;

/* @var View $this */
/* @var Client $client */
/* @var string $secret */
?>
<?php $this->beginContent('@admin/views/authentication/_authenticationLayout.php') ?>
<?= $this->render('_tabs') ?>
<div class="panel-body">
    <h4><?= Yii::t('OauthModule.base', 'Secret of “{name}”', ['name' => Html::encode($client->name)]) ?></h4>
    <p class="text-body-secondary">
        <?= Yii::t('OauthModule.base', 'Copy the secret now. You won\'t be able to see it again.') ?>
    </p>

    <div class="mb-3">
        <label class="form-label" for="oauth-client-id"><?= Yii::t('OauthModule.base', 'Client ID') ?></label>
        <input type="text" id="oauth-client-id" class="form-control font-monospace" readonly
               value="<?= Html::encode($client->client_id) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label" for="oauth-client-secret"><?= Yii::t('OauthModule.base', 'Client secret') ?></label>
        <div class="input-group">
            <input type="text" id="oauth-client-secret" class="form-control font-monospace" readonly
                   value="<?= Html::encode($secret) ?>">
            <?= Button::light(Yii::t('OauthModule.base', 'Copy'))
                ->icon('copy')
                ->id('oauth-copy-secret')
                ->loader(false) ?>
        </div>
    </div>

    <?= Button::primary(Yii::t('OauthModule.base', 'Done'))->link(['/oauth/admin/clients']) ?>
</div>

<script <?= Html::nonce() ?>>
    $(function () {
        const $secret = $('#oauth-client-secret');
        $('#oauth-client-id, #oauth-client-secret').on('focus click', function () {
            this.select();
        });
        $('#oauth-copy-secret').on('click', function (event) {
            event.preventDefault();
            $secret.trigger('select');
            navigator.clipboard.writeText($secret.val()).then(function () {
                humhub.modules.ui.status.success(<?= json_encode(Yii::t('OauthModule.base', 'The secret has been copied to your clipboard.'), JSON_HEX_TAG) ?>);
            });
        });
    });
</script>
<?php $this->endContent() ?>
