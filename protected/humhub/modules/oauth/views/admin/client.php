<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\models\forms\ClientForm;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;

/* @var View $this */
/* @var ClientForm $model */

$client = $model->client;
$action = $client->isNewRecord ? ['/oauth/admin/client'] : ['/oauth/admin/client', 'id' => $client->id];
?>
<?php $this->beginContent('@admin/views/authentication/_authenticationLayout.php') ?>
<?= $this->render('_tabs') ?>
<div class="panel-body">
    <h4>
        <?= $client->isNewRecord
            ? Yii::t('OauthModule.base', 'Add client')
            : Yii::t('OauthModule.base', 'Edit client “{name}”', ['name' => Html::encode($client->name)]) ?>
    </h4>

    <?php if (!$client->isNewRecord): ?>
        <p>
            <?= Yii::t('OauthModule.base', 'Client ID') ?>:
            <span class="font-monospace"><?= Html::encode($client->client_id) ?></span>
        </p>
    <?php endif ?>

    <?php $form = ActiveForm::begin(['action' => $action, 'id' => 'oauth-client-form']) ?>
    <?= $form->errorSummary($model) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 100]) ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 2, 'maxlength' => 1000]) ?>
    <?= $form->field($model, 'redirectUris')->textarea(['rows' => 3, 'class' => 'form-control font-monospace'])
        ->hint(Yii::t('OauthModule.base', 'https:// URIs, loopback URIs such as http://127.0.0.1/callback (any port) or, for public clients, a reverse-domain scheme such as com.example.app:/callback.')) ?>
    <?= $form->field($model, 'confidential')->checkbox() ?>
    <?php if ($model->hasLockedFullAccess()): ?>
        <div class="mb-3">
            <label class="form-label"><?= Html::encode($model->getAttributeLabel('scopes')) ?></label>
            <p><?= Html::encode(ScopeRegistry::get(ScopeRegistry::FULL)->label) ?></p>
            <div class="form-text"><?= Yii::t('OauthModule.base', 'Set by a system administrator.') ?></div>
        </div>
    <?php else: ?>
        <?= $form->field($model, 'scopes')->checkboxList($model->getScopeOptions()) ?>
    <?php endif ?>
    <?php if ($model->canSetPrivileged()): ?>
        <?= $form->field($model, 'skipConsent')->checkbox() ?>
    <?php endif ?>
    <?= $form->field($model, 'isActive')->checkbox() ?>

    <?= Button::save()->submit() ?>
    <?= Button::light(Yii::t('base', 'Cancel'))->link(['/oauth/admin/clients']) ?>
    <?php ActiveForm::end() ?>
</div>

<?php if ($client->isConfidential()): ?>
    <div class="panel-body">
        <hr>
        <h4><?= Yii::t('OauthModule.base', 'Client secret') ?></h4>
        <p class="text-body-secondary">
            <?= Yii::t('OauthModule.base', 'A new secret replaces the current one immediately. The client has to be updated with it.') ?>
        </p>

        <?= Button::danger(Yii::t('OauthModule.base', 'Regenerate secret'))
            ->link(['/oauth/admin/regenerate-secret', 'id' => $client->id])
            ->options(['data-method' => 'POST'])
            ->confirm(
                Yii::t('OauthModule.base', 'Regenerate the secret?'),
                Yii::t('OauthModule.base', 'The current secret stops working immediately.'),
            ) ?>
    </div>
<?php endif ?>
<?php $this->endContent() ?>
