<?php

use humhub\helpers\Html;
use humhub\modules\oauth\permissions\CreatePersonalAccessToken;
use humhub\widgets\bootstrap\Alert;
use yii\helpers\Url;
use humhub\modules\oauth\models\forms\SettingsForm;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\form\MultiSelect;

/* @var $this \humhub\components\View */
/* @var $model SettingsForm */
?>

<?php $this->beginContent('@admin/views/authentication/_authenticationLayout.php') ?>
<?= $this->render('_tabs') ?>
<div class="panel-body">
    <?php $form = ActiveForm::begin(['id' => 'oauth-settings-form', 'acknowledge' => true]); ?>
        <?= $form->errorSummary($model) ?>

        <h4><?= Yii::t('OauthModule.base', 'HumHub app') ?></h4>
        <?= $form->field($model, 'allowApp')->checkbox() ?>

        <hr>
        <h4><?= Yii::t('OauthModule.base', 'Clients') ?></h4>
        <?= $form->field($model, 'allowClients')->checkbox() ?>

        <hr>
        <h4><?= Yii::t('OauthModule.base', 'Personal access tokens') ?></h4>
        <?= $form->field($model, 'allowPersonalAccessTokens')->checkbox() ?>
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
        ]) ?>
        <?= $form->field($model, 'patMaxLifetimeDays')->textInput(['type' => 'number', 'min' => 0]) ?>

        <hr>
        <h4><?= Yii::t('OauthModule.base', 'Lifetimes and revocation') ?></h4>
        <?= $form->field($model, 'refreshTokenLifetimeDays')->textInput(['type' => 'number', 'min' => 1]) ?>
        <?= $form->field($model, 'grantMaxLifetimeDays')->textInput(['type' => 'number', 'min' => 0]) ?>
        <?= $form->field($model, 'revokeOnPasswordChange')->checkbox() ?>

        <hr>
        <?= Html::submitButton(Yii::t('OauthModule.base', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']) ?>
    <?php ActiveForm::end() ?>
</div>
<?php $this->endContent(); ?>
