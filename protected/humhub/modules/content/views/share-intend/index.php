<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\content\models\forms\ShareIntendTargetForm;
use humhub\modules\space\widgets\SpacePickerField;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\ui\view\components\View;
use humhub\widgets\Button;
use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;

/**
 * @var $this View
 * @var $model ShareIntendTargetForm
 */
?>

<?php ModalDialog::begin([
    'header' => Yii::t('ContentModule.base', 'Select a target'),
]) ?>

<?php $form = ActiveForm::begin() ?>

<div class="modal-body">
    <p>ToDo: Integrate Profile Option (in Picker via ContentContainerPicker?)</p>

    <?= $form->field($model, 'targetSpaceGuid')->widget(SpacePickerField::class, [
        'maxSelection' => 1,
        'focus' => true,
        'url' => $model->getSpaceSearchUrl()
    ]) ?>
</div>

<div class="modal-footer">
    <?= ModalButton::cancel() ?>
    <?= Button::primary(Yii::t('base', 'Save'))
        ->action('ui.modal.submit', ['index'])->submit()->loader(true) ?>
</div>

<?php ActiveForm::end() ?>
<?php ModalDialog::end() ?>
