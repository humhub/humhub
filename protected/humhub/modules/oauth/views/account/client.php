<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\oauth\components\RecentAuthentication;
use humhub\modules\oauth\models\forms\ClientForm;
use humhub\modules\oauth\models\forms\ReauthenticationForm;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;

/* @var View $this */
/* @var ClientForm $model */
/* @var ReauthenticationForm $reauthentication */

$client = $model->client;
$action = $client->isNewRecord ? ['/oauth/account/client'] : ['/oauth/account/client', 'id' => $client->id];
?>
<?php $this->beginContent('@user/views/security/_layout.php') ?>
<section class="security-section mb-4">
    <h2 class="h5 mb-1">
        <?= $client->isNewRecord
            ? Yii::t('OauthModule.base', 'Register app')
            : Yii::t('OauthModule.base', 'Edit app “{name}”', ['name' => Html::encode($client->name)]) ?>
    </h2>
    <p class="text-body-secondary">
        <?= Yii::t('OauthModule.base', 'An app registered here can only be connected to your own account.') ?>
    </p>

    <?php if (!$client->isNewRecord): ?>
        <p>
            <?= Yii::t('OauthModule.base', 'Client ID') ?>:
            <span class="font-monospace"><?= Html::encode($client->client_id) ?></span>
        </p>
    <?php endif ?>

    <?php $form = ActiveForm::begin(['action' => $action, 'id' => 'oauth-client-form']) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 100]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 2, 'maxlength' => 1000]) ?>

    <?= $form->field($model, 'redirectUris')->textarea(['rows' => 3, 'class' => 'form-control font-monospace'])
        ->hint(Yii::t('OauthModule.base', 'https:// URIs, loopback URIs such as http://127.0.0.1/callback (any port) or, for public clients, a reverse-domain scheme such as com.example.app:/callback.')) ?>

    <?= $form->field($model, 'confidential')->checkbox() ?>

    <?= $form->field($model, 'scopes')->checkboxList($model->getScopeOptions()) ?>

    <?= Button::save()->submit() ?>
    <?= Button::light(Yii::t('base', 'Cancel'))->link(['/oauth/account/index']) ?>

    <?php ActiveForm::end() ?>
</section>

<?php if ($client->isConfidential()): ?>
    <section class="security-section mb-4">
        <h2 class="h5 mb-1"><?= Yii::t('OauthModule.base', 'Client secret') ?></h2>
        <p class="text-body-secondary">
            <?= Yii::t('OauthModule.base', 'A new secret replaces the current one immediately. The app has to be updated with it.') ?>
        </p>

        <?php $form = ActiveForm::begin(['action' => ['/oauth/account/regenerate-secret', 'id' => $client->id], 'id' => 'oauth-secret-form']) ?>

        <?php if ($reauthentication->requiresPassword()): ?>
            <?= $form->field($reauthentication, 'currentPassword')->passwordInput(['autocomplete' => 'current-password']) ?>
        <?php else: ?>
            <p class="text-body-secondary">
                <?= Yii::t('OauthModule.base', 'For your security, you must have signed in within the last {minutes} minutes.', ['minutes' => RecentAuthentication::MAX_LOGIN_AGE / 60]) ?>
            </p>
            <?= $form->field($reauthentication, 'currentPassword', ['template' => '{error}'])->hiddenInput() ?>
        <?php endif ?>

        <?= Button::danger(Yii::t('OauthModule.base', 'Regenerate secret'))->submit() ?>

        <?php ActiveForm::end() ?>
    </section>
<?php endif ?>
<?php $this->endContent() ?>
