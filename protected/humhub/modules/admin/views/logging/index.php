<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\libs\DateHelper;
use humhub\modules\admin\assets\LogAsset;
use humhub\modules\admin\models\forms\LogFilterForm;
use humhub\modules\admin\models\Log;
use humhub\modules\ui\form\widgets\DatePicker;
use humhub\modules\ui\form\widgets\MultiSelect;
use humhub\widgets\form\ActiveForm;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\log\Logger;

/* @var $logEntries Log[] */
/* @var $pagination Pagination */
/* @var $filter LogFilterForm */
/* @var $this View */

LogAsset::register($this);

if ($filter->day) {
    // Workaround since 10/03/2020 is changed to 03/10/2020 e.g. in UK english
    $filter->day = DateHelper::parseDateTime($filter->day);
}

?>

<style>
    #admin-log-root .select2-selection__choice[title="<?= Html::encode(LogFilterForm::getLevelLabel(Logger::LEVEL_ERROR)) ?>"] {
        background-color: var(--danger);
    }

    #admin-log-root .select2-selection__choice[title="<?= Html::encode(LogFilterForm::getLevelLabel(Logger::LEVEL_WARNING)) ?>"] {
        background-color: var(--warning);
    }

    #admin-log-root .select2-selection__choice[title="<?= Html::encode(LogFilterForm::getLevelLabel(Logger::LEVEL_INFO)) ?>"] {
        background-color: var(--info);
    }
</style>

<div id="admin-log-root">
    <div data-ui-widget="admin.log.LogFilterForm" data-ui-init="1">
        <?php $form = ActiveForm::begin([
            'action' => Url::to(['/admin/logging/index']),
            'options' => ['class' => 'row'],
        ]) ?>

        <div class="col-lg-3 col-lg-push-1" style="padding-right:0">
            <?= $form->field($filter, 'term')->textInput(
                [
                    'placeholder' => Yii::t('AdminModule.information', 'Search term...'),
                    'maxlength' => 200,
                ]
            )->label(false) ?>
        </div>
        <div class="col-lg-2" style="padding-right:0">
            <?= $form->field($filter, 'day')->widget(DatePicker::class, [
                'dateFormat' => Yii::$app->formatter->dateInputFormat,
                'options' => ['placeholder' => Yii::t('AdminModule.information', 'Select day')],
            ])->label(false) ?>
        </div>
        <div class="col-lg-4" style="padding-right:0">
            <?= $form->field($filter, 'levels')->widget(MultiSelect::class, [
                'items' => $filter->getLevelSelection(),
                'placeholderMore' => Yii::t('AdminModule.information', 'Select level...')
            ])->label(false) ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($filter, 'category')->dropDownList($filter->getCategorySelection())->label(false) ?>
        </div>

        <?php ActiveForm::end() ?>
    </div>

    <?= $this->render('log_entries', ['pagination' => $pagination, 'logEntries' => $logEntries]) ?>
</div>
