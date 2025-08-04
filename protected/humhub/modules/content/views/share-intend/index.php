<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\modules\content\models\forms\ShareIntendTargetForm;
use humhub\modules\content\widgets\ContentContainerPickerField;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/**
 * @var $this View
 * @var $model ShareIntendTargetForm
 * @var $fileList string[]
 */
?>

<?php $form = Modal::beginFormDialog([
    'title' => Yii::t('ContentModule.base', 'Share'),
    'footer' => ModalButton::light(Yii::t('base', 'Back'))
        ->load(['/file/share-intend', 'fileList' => $fileList]),
]) ?>

    <?= $form->field($model, 'targetContainerGuid')->widget(ContentContainerPickerField::class, [
        'maxSelection' => 1,
        'minInput' => 0,
        'focus' => true,
        'url' => $model->getContainerSearchUrl(),
        'options' => ['data-action-change' => 'ui.modal.submit'],
    ])->label(false) ?>

<?php Modal::endFormDialog() ?>
