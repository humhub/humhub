<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\libs\TimezoneHelper;
use humhub\modules\admin\models\forms\AppearanceGeneralForm;
use humhub\widgets\ContentCard;
use humhub\widgets\form\ActiveForm;

/**
 * @var $this View
 * @var $model AppearanceGeneralForm
 */

$allowedLanguages = Yii::$app->i18n->getAllowedLanguages();
?>
<?php ContentCard::begin(['title' => Yii::t('AdminModule.base', 'General')]) ?>
<?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

<?= $form->field($model, 'name') ?>

<?php if (count($allowedLanguages) > 1) : ?>
    <?= $form->field($model, 'defaultLanguage')->dropDownList($allowedLanguages) ?>
<?php endif; ?>

<?= $form->field($model, 'defaultTimeZone')->dropDownList(
    TimezoneHelper::generateList(true),
    ['disabled' => Yii::$app->settings->isFixed('defaultTimeZone')],
) ?>

<?= $form->field($model, 'dateInputDisplayFormat')->dropDownList($model->getDateInputDisplayFormats()) ?>

<hr>
<?= Html::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']) ?>

<?php ActiveForm::end(); ?>
<?php ContentCard::end() ?>
