<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\forms\ScheduleOptionsForm;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\ui\form\widgets\DatePicker;
use humhub\modules\ui\form\widgets\TimePicker;
use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;

/* @var ScheduleOptionsForm $scheduleOptions */
?>
<?php ModalDialog::begin(['header' => Yii::t('ContentModule.modules', '<strong>Schedule</strong> options')]) ?>

    <?php $form = ActiveForm::begin() ?>
    <?= Html::hiddenInput('state', Content::STATE_SCHEDULED) ?>
    <?= Html::hiddenInput('stateTitle', $scheduleOptions->getStateTitle()) ?>
    <?= Html::hiddenInput('scheduled_at', $scheduleOptions->date) ?>

    <div class="modal-body">
        <?= $form->field($scheduleOptions, 'enabled')->checkbox() ?>
        <div class="row">
            <div class="col-sm-3 col-xs-6">
                <?= $form->field($scheduleOptions, 'date')
                    ->widget(DatePicker::class, ['options' => ['disabled' => !$scheduleOptions->enabled]])
                    ->label(false) ?>
            </div>
            <div class="col-sm-3 col-xs-6" style="padding-left:0">
                <?= $form->field($scheduleOptions, 'time')
                    ->widget(TimePicker::class, ['disabled' => !$scheduleOptions->enabled])
                    ->label(false) ?>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <?= ModalButton::submitModal() ?>
        <?= ModalButton::cancel() ?>
    </div>

    <?php ActiveForm::end() ?>

<?php ModalDialog::end() ?>