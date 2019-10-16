<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;
use yii\bootstrap\ActiveForm;

/* @var $this \humhub\components\View */
/* @var $model \humhub\modules\topic\models\Topic */
?>

<?php ModalDialog::begin(['header' => Yii::t('TopicModule.base', '<strong>Edit</strong> Topic')])?>
    <?php $form = ActiveForm::begin() ?>
        <div class="modal-body">
            <?= $form->field($model, 'name')?>
            <?= $form->field($model, 'sort_order')->textInput( ['type' => 'number'])?>
        </div>
        <div class="modal-footer">
            <?= ModalButton::submitModal()?>
            <?= ModalButton::cancel() ?>
        </div>
    <?php ActiveForm::end() ?>
<?php ModalDialog::end()?>
