<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\admin\assets\AdminAsset;
use humhub\modules\admin\models\forms\DarkModeSettingsForm;
use humhub\widgets\bootstrap\Alert;
use humhub\widgets\ContentCard;
use humhub\widgets\form\ActiveForm;

/**
 * @var $this View
 * @var $model DarkModeSettingsForm
 */

AdminAsset::register($this);

$logoDark = Yii::$app->img->logoDark;
?>
<?php ContentCard::begin(['title' => Yii::t('AdminModule.base', 'Dark Mode')]) ?>
<?= Alert::info(Yii::t('AdminModule.settings', 'The dark mode is experimental: some pages and modules may not be adapted yet.'))
    ->closeButton(false) ?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data'], 'acknowledge' => true]); ?>

<?= $form->field($model, 'allowDark')->checkbox() ?>

<fieldset id="dark-mode-options" <?= $model->allowDark ? '' : 'disabled' ?>>
    <?= $form->field($model, 'defaultColorMode')->dropDownList($model->getDefaultColorModes()) ?>

    <?= $this->render('_imageUpload', [
        'form' => $form,
        'model' => $model,
        'attribute' => 'logoDark',
        'key' => 'logoDark',
        'url' => $logoDark->getUrl(),
        'exists' => $logoDark->exists(),
        'changeAction' => 'admin.changeLogoDark',
        'deleteAction' => 'admin.deleteLogoDark',
        'deleteImage' => 'logo-dark',
    ]) ?>
</fieldset>

<hr>
<?= Html::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']) ?>

<?php ActiveForm::end(); ?>
<?php ContentCard::end() ?>

<script <?= Html::nonce() ?>>
    $(function () {
        $('#<?= Html::getInputId($model, 'allowDark') ?>').on('change', function () {
            $('#dark-mode-options').prop('disabled', !this.checked);
        });
    })
</script>
