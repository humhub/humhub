<?php

use humhub\helpers\Html;
use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\models\forms\SettingsForm;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\permissions\CreatePersonalAccessToken;
use humhub\widgets\bootstrap\Alert;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\form\MultiSelect;
use yii\helpers\Url;

/* @var $this \humhub\components\View */
/* @var $model SettingsForm */
?>

<?php $this->beginContent('@admin/views/authentication/_authenticationLayout.php') ?>
<?= $this->render('_tabs') ?>
<?php $form = ActiveForm::begin(['id' => 'oauth-settings-form', 'acknowledge' => true]); ?>
    <?= $form->errorSummary($model) ?>

    <?= $form->beginCollapsibleFields(Yii::t('OauthModule.base', 'HumHub app'), false) ?>
    <?= $form->field($model, 'allowApp')->checkbox() ?>
    <?php if ($model->canEditAppIds()): ?>
        <?= $form->field($model, 'appIds')->textarea(['rows' => 3, 'class' => 'form-control font-monospace']) ?>
    <?php else: ?>
        <div class="mb-3" id="oauth-app-ids">
            <label class="form-label"><?= Html::encode($model->getAttributeLabel('appIds')) ?></label>
            <p class="font-monospace"><?= $model->getAppIdList() === []
                ? Yii::t('OauthModule.base', 'None')
                : implode('<br>', array_map([Html::class, 'encode'], $model->getAppIdList())) ?></p>
            <div class="form-text">
                <?= $model->getAttributeHint('appIds') ?>
                <?= Module::instance()->isAppIdsConfigured()
                    ? Yii::t('OauthModule.base', 'Set in the configuration file.')
                    : Yii::t('OauthModule.base', 'Only system administrators can change this.') ?>
            </div>
        </div>
    <?php endif; ?>
    <?= $form->endCollapsibleFields() ?>

    <?= $form->beginCollapsibleFields(Yii::t('OauthModule.base', 'Clients')) ?>
    <?= $form->field($model, 'allowClients')->checkbox() ?>
    <?= $form->endCollapsibleFields() ?>

    <?= $form->beginCollapsibleFields(Yii::t('OauthModule.base', 'Personal access tokens')) ?>
    <?= $form->field($model, 'allowPersonalAccessTokens')->checkbox() ?>
    <div id="oauth-pat-settings"<?= $model->allowPersonalAccessTokens ? '' : ' class="d-none"' ?>>
        <?php if ($model->allowPersonalAccessTokens && !$model->isGrantedToAnyGroup(CreatePersonalAccessToken::class)): ?>
            <?= Alert::warning(Yii::t('OauthModule.base', 'No group has the permission "{permission}" yet, so no user can use this. Allow it under <a href="{url}">Groups → Permissions</a>.', [
                'permission' => Html::encode((new CreatePersonalAccessToken())->getTitle()),
                'url' => Html::encode(Url::to(['/admin/group'])),
            ]))->closeButton(false) ?>
        <?php endif; ?>
        <?= $form->field($model, 'patScopes')->widget(MultiSelect::class, [
            'items' => $model->getScopeOptions(),
            'placeholderMore' => '',
            'options' => ['data-tags' => 'false'],
        ])->hint($model->hasLockedFullAccess()
            ? $model->getAttributeHint('patScopes') . ' ' . Yii::t('OauthModule.base', '"{scope}" is included, set by a system administrator.', [
                'scope' => Html::encode(ScopeRegistry::get(ScopeRegistry::FULL)->label),
            ])
            : null) ?>
        <?= $form->field($model, 'patMaxLifetimeDays')->textInput(['type' => 'number', 'min' => 0]) ?>
        <?= $form->field($model, 'maxPersonalAccessTokensPerUser')->textInput([
            'type' => 'number',
            'min' => 0,
            'readonly' => Module::instance()->isMaxPersonalAccessTokensPerUserConfigured(),
        ])->hint($model->getAttributeHint('maxPersonalAccessTokensPerUser') . (Module::instance()->isMaxPersonalAccessTokensPerUserConfigured()
            ? ' ' . Yii::t('OauthModule.base', 'Set in the configuration file.')
            : '')) ?>
    </div>
    <?= $form->endCollapsibleFields() ?>

    <?= $form->beginCollapsibleFields(Yii::t('OauthModule.base', 'Lifetimes and revocation')) ?>
    <?= $form->field($model, 'refreshTokenLifetimeDays')->textInput(['type' => 'number', 'min' => 1]) ?>
    <?= $form->field($model, 'grantMaxLifetimeDays')->textInput(['type' => 'number', 'min' => 0]) ?>
    <?= $form->field($model, 'revokeOnPasswordChange')->checkbox() ?>
    <?= $form->endCollapsibleFields() ?>

    <?= Html::submitButton(Yii::t('OauthModule.base', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']) ?>
<?php ActiveForm::end() ?>
<?php $this->endContent(); ?>

<script <?= Html::nonce() ?>>
    $(function () {
        $('#<?= Html::getInputId($model, 'allowPersonalAccessTokens') ?>').on('change', function () {
            $('#oauth-pat-settings').toggleClass('d-none', !$(this).is(':checked'));
        });
    });
</script>
