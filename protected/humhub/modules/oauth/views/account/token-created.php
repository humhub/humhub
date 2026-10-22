<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\widgets\bootstrap\Button;

/* @var View $this */
/* @var string $token */
/* @var string $name */
?>
<?php $this->beginContent('@user/views/security/_layout.php') ?>
<section class="security-section mb-4">
    <h2 class="h5 mb-1"><?= Yii::t('OauthModule.base', 'Token “{name}” created', ['name' => Html::encode($name)]) ?></h2>
    <p class="text-body-secondary">
        <?= Yii::t('OauthModule.base', 'Copy the token now. You won\'t be able to see it again.') ?>
    </p>

    <div class="input-group mb-3">
        <input type="text" id="oauth-created-token" class="form-control font-monospace" readonly
               value="<?= Html::encode($token) ?>"
               aria-label="<?= Html::encode(Yii::t('OauthModule.base', 'Personal access token')) ?>">
        <?= Button::light(Yii::t('OauthModule.base', 'Copy'))
            ->icon('copy')
            ->id('oauth-copy-token')
            ->loader(false) ?>
    </div>

    <?= Button::primary(Yii::t('OauthModule.base', 'Done'))->link(['/oauth/account/index']) ?>
</section>

<script <?= Html::nonce() ?>>
    $(function () {
        const $token = $('#oauth-created-token');
        $token.on('focus click', function () {
            this.select();
        });
        $('#oauth-copy-token').on('click', function (event) {
            event.preventDefault();
            $token.trigger('select');
            navigator.clipboard.writeText($token.val()).then(function () {
                humhub.modules.ui.status.success(<?= json_encode(Yii::t('OauthModule.base', 'The token has been copied to your clipboard.'), JSON_HEX_TAG) ?>);
            });
        });
    });
</script>
<?php $this->endContent() ?>
