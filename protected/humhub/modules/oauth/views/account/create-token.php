<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\oauth\models\forms\PersonalAccessTokenForm;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;

/* @var View $this */
/* @var PersonalAccessTokenForm $model */
?>
<?php $this->beginContent('@user/views/security/_layout.php') ?>
<section class="settings-section mb-4">
    <h3><?= Yii::t('OauthModule.base', 'Create token') ?></h3>
    <p>
        <?= Yii::t('OauthModule.base', 'A personal access token lets a script or agent use the API with your account. Only grant the access it needs.') ?>
    </p>

    <?php $form = ActiveForm::begin(['action' => ['/oauth/account/create-token'], 'sudo' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 100, 'autocomplete' => 'off']) ?>

    <?= $form->field($model, 'scopes')->checkboxList($model->getScopeOptions()) ?>
    <?= $this->render('@oauth/views/common/_full-scope', [
        'listId' => Html::getInputId($model, 'scopes'),
        'scopeOptions' => $model->getScopeOptions(),
    ]) ?>

    <?= $form->field($model, 'lifetime')->dropDownList($model->getLifetimeOptions()) ?>

    <?= Button::save(Yii::t('OauthModule.base', 'Create token'))->submit() ?>
    <?= Button::light(Yii::t('base', 'Cancel'))->link(['/user/security/sessions']) ?>

    <?php ActiveForm::end() ?>
</section>
<?php $this->endContent() ?>
