<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\modules\topic\models\Topic;
use humhub\modules\ui\view\components\View;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\form\SortOrderField;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var $this View */
/* @var $model Topic */
?>

<?php $form = ActiveForm::begin() ?>

<?php Modal::beginDialog([
    'header' => Yii::t('TopicModule.base', '<strong>Edit</strong> Topic'),
    'footer' => ModalButton::cancel() . ' ' . ModalButton::submitModal(),
]) ?>

<?= $form->field($model, 'name') ?>
<?= $form->field($model, 'sort_order')->widget(SortOrderField::class) ?>

<?php Modal::endDialog() ?>

<?php ActiveForm::end() ?>
