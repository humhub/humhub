<?php
use humhub\widgets\ModalDialog;
use yii\bootstrap\ActiveForm;
use humhub\modules\space\widgets\SpacePickerField;
use humhub\widgets\ModalButton;
use humhub\widgets\Button;

/* @var $model \humhub\modules\content\models\forms\MoveContentForm */

?>

<?php ModalDialog::begin(['header' => Yii::t('ContentModule.base', '<strong>Move</strong> content')]) ?>
 <?php $form = ActiveForm::begin() ?>
    <div class="modal-body">
      <?= $form->field($model, 'target')->widget(SpacePickerField::class, [
              'maxSelection' => 1,
              'focus' => true,
              'url' => $model->getSearchUrl()
      ])?>
    </div>
    <div class="modal-footer">
        <?= Button::primary(Yii::t('base', 'Save'))->action('content.submitMove')->loader(true) ?>
        <?= ModalButton::cancel() ?>
    </div>
 <?php ActiveForm::end() ?>
<?php ModalDialog::end() ?>
