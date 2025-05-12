<?php
/**
 * @var $this \humhub\components\View
 * @var FileSettingsForm $model
 */

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\admin\models\forms\FileSettingsForm;
use humhub\modules\file\Module;
use humhub\widgets\form\ActiveForm;

/** @var Module $fileModule */
$fileModule = Yii::$app->getModule('file');

?>
<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

<?= $form->field($model, 'maxFileSize')->textInput(['class' => 'form-control', 'readonly' => $fileModule->settings->isFixed('maxFileSize')]); ?>
<?= $form->field($model, 'allowedExtensions')->textarea(['class' => 'form-control']); ?>
<br/>
<?= $form->field($model, 'useXSendfile')->checkbox(['disabled' => $fileModule->settings->isFixed('useXSendfile')]); ?>
<?= $form->field($model, 'excludeMediaFilesPreview')->checkbox(['disabled' => $fileModule->settings->isFixed('excludeMediaFilesPreview')]); ?>

<hr>
<?= Html::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

<?php ActiveForm::end(); ?>

<?php $this->endContent(); ?>
