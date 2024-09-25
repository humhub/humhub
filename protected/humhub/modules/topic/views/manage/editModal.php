<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\modules\topic\models\Topic;
use humhub\modules\ui\form\widgets\SortOrderField;
use humhub\modules\ui\view\components\View;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var $this View */
/* @var $model Topic */
?>

<?php Modal::beginDialog(['header' => Yii::t('TopicModule.base', '<strong>Edit</strong> Topic')]) ?>
<?php $form = ActiveForm::begin() ?>
<div class="modal-body">
    <?= $form->field($model, 'name') ?>
    <?= $form->field($model, 'sort_order')->widget(SortOrderField::class) ?>
</div>
<div class="modal-footer">
    <?= ModalButton::cancel() ?>
    <?= ModalButton::submitModal() ?>
</div>
<?php ActiveForm::end() ?>
<?php Modal::endDialog() ?>
