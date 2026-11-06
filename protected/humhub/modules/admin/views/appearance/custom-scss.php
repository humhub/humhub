<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\admin\models\forms\CustomScssForm;
use humhub\widgets\ContentCard;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\form\CodeMirrorInputWidget;

/**
 * @var $this View
 * @var $model CustomScssForm
 */
?>
<?php ContentCard::begin(['title' => Yii::t('AdminModule.base', 'Custom SCSS')]) ?>
<?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

<?= $form->field($model, 'themeCustomScss')->widget(CodeMirrorInputWidget::class, ['mode' => 'text/x-scss']) ?>

<hr>
<?= Html::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']) ?>

<?php ActiveForm::end(); ?>
<?php ContentCard::end() ?>
