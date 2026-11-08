<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\admin\models\forms\AccessibilitySettingsForm;
use humhub\widgets\ContentCard;
use humhub\widgets\form\ActiveForm;

/**
 * @var $this View
 * @var $model AccessibilitySettingsForm
 */
?>
<?php ContentCard::begin(['title' => Yii::t('AdminModule.base', 'Accessibility')]) ?>
<p class="text-body-secondary">
    <?= Yii::t('AdminModule.settings', 'The defaults for guests and for users who did not choose themselves. Users can change them under Account → Appearance & Accessibility.') ?>
</p>
<?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

<?= $form->field($model, 'contrast')->dropDownList($model->getOptions()) ?>
<?= $form->field($model, 'motion')->dropDownList($model->getOptions()) ?>
<?= $form->field($model, 'font')->checkbox() ?>

<hr>
<?= $form->field($model, 'imageAltText')->dropDownList($model->getImageAltTextOptions()) ?>

<hr>
<?= Html::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']) ?>

<?php ActiveForm::end(); ?>
<?php ContentCard::end() ?>
