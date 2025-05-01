<?php
/**
 * @var $this View
 * @var FileSettingsForm $model
 */

use humhub\modules\admin\models\forms\FileSettingsForm;
use humhub\modules\file\Module;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\view\components\View;
use yii\helpers\Html;

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

<?= $form->beginCollapsibleFields(Yii::t('AdminModule.settings', 'Well-known files')) ?>
    <?php if (!Yii::$app->urlManager->enablePrettyUrl) : ?>
        <div class="alert alert-warning">
            <?= Icon::get('warning') ?>
            <?= Yii::t('AdminModule.settings', 'Please enable <a href="{url}" target="_blank">Pretty URLs</a> for proper working of the well-known files.', [
                'url' => 'https://docs.humhub.org/docs/admin/installation/#pretty-urls',
            ]) ?>
        </div>
    <?php endif; ?>

    <?= $form->field($model, 'fileAssetLinks')->textarea(['rows' => 10]) ?>
    <?= $form->field($model, 'fileAppleAssociation')->textarea(['rows' => 10]) ?>
<?= $form->endCollapsibleFields() ?>

<hr>
<?= Html::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

<?php ActiveForm::end(); ?>

<?php $this->endContent(); ?>
