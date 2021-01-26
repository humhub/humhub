<?php

use humhub\modules\ui\form\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $logsCount integer */
/* @var $dating string */
/* @var $limitAgeOptions array */
/* @var $model \humhub\modules\admin\models\forms\LogFilterForm */
?>
<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<p><?= Yii::t('AdminModule.settings',
        'Old logs can significantly increase the size of your database while providing little information.') ?>
</p>
<p><?= Yii::t('AdminModule.settings',
        'Currently there are {count} records in the database dating from {dating}.',
        ['count' => $logsCount, 'dating' => $dating]) ?>
</p>
<br>

<?php $form = ActiveForm::begin(); ?>

<?= $form->errorSummary($model); ?>
<?= $form->field($model, 'logsDateLimit')->dropDownList($limitAgeOptions, ['class' => 'form-control']); ?>

<hr>

<?= Html::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

<?php ActiveForm::end(); ?>

<?php $this->endContent(); ?>
