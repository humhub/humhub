<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\content\models\forms\ShareIntendTargetForm;
use humhub\modules\content\widgets\ContentContainerPickerField;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\ui\view\components\View;
use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;

/**
 * @var $this View
 * @var $model ShareIntendTargetForm
 * @var $fileList string[]
 */
?>

<?php ModalDialog::begin([
    'header' => Yii::t('ContentModule.base', 'Share'),
]) ?>

<?php $form = ActiveForm::begin() ?>

<div class="modal-body">
    <?= $form->field($model, 'targetContainerGuid')->widget(ContentContainerPickerField::class, [
        'maxSelection' => 1,
        'minInput' => 0,
        'focus' => true,
        'url' => $model->getContainerSearchUrl(),
        'options' => ['data-action-change' => 'ui.modal.submit'],
    ])->label(false) ?>
</div>

<div class="modal-footer">
    <?= ModalButton::defaultType(Yii::t('base', 'Back'))
        ->load(['/file/share-intend', 'fileList' => $fileList]) ?>
</div>

<?php ActiveForm::end() ?>
<?php ModalDialog::end() ?>
