<?php

use humhub\components\View;
use humhub\modules\oauth\components\RecentAuthentication;
use humhub\modules\oauth\models\forms\PersonalAccessTokenForm;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;

/* @var View $this */
/* @var PersonalAccessTokenForm $model */
?>
<?php $this->beginContent('@user/views/security/_layout.php') ?>
<section class="security-section mb-4">
    <h2 class="h5 mb-1"><?= Yii::t('OauthModule.base', 'Create token') ?></h2>
    <p class="text-body-secondary">
        <?= Yii::t('OauthModule.base', 'A personal access token lets a script or agent use the API with your account. Only grant the access it needs.') ?>
    </p>

    <?php $form = ActiveForm::begin(['action' => ['/oauth/account/create-token']]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 100, 'autocomplete' => 'off']) ?>

    <?= $form->field($model, 'scopes')->checkboxList($model->getScopeOptions()) ?>

    <?= $form->field($model, 'lifetime')->dropDownList($model->getLifetimeOptions()) ?>

    <?php if ($model->requiresPassword()): ?>
        <?= $form->field($model, 'currentPassword')->passwordInput(['autocomplete' => 'current-password']) ?>
    <?php else: ?>
        <p class="text-body-secondary">
            <?= Yii::t('OauthModule.base', 'For your security, you must have signed in within the last {minutes} minutes.', ['minutes' => RecentAuthentication::MAX_LOGIN_AGE / 60]) ?>
        </p>
        <?= $form->field($model, 'currentPassword', ['template' => '{error}'])->hiddenInput() ?>
    <?php endif ?>

    <?= Button::save(Yii::t('OauthModule.base', 'Create token'))->submit() ?>
    <?= Button::light(Yii::t('base', 'Cancel'))->link(['/user/security/sessions']) ?>

    <?php ActiveForm::end() ?>
</section>
<?php $this->endContent() ?>
