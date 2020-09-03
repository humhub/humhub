<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\modules\topic\widgets\TopicPicker;
use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;
use yii\bootstrap\ActiveForm;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $model \humhub\modules\topic\models\forms\ContentTopicsForm */

?>

<?php ModalDialog::begin(['header' => Yii::t('TopicModule.base', '<strong>Manage</strong> Topics')]) ?>
    <?php $form = ActiveForm::begin() ?>
        <div class="modal-body">
            <?=  $form->field($model, 'topics')->widget(TopicPicker::class, ['contentContainer' => $model->getContentContainer()])->label(false) ?>
        </div>

        <div class="modal-footer">
            <?= ModalButton::submitModal()?>
            <?= ModalButton::cancel() ?>
        </div>
    <?php ActiveForm::end() ?>
<?php ModalDialog::end()?>
