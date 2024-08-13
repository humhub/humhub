<?php
use humhub\widgets\ModalDialog;
use yii\bootstrap\ActiveForm;
use humhub\modules\space\widgets\SpacePickerField;
use humhub\widgets\ModalButton;
use humhub\widgets\Button;

/* @var $model \humhub\modules\content\models\forms\MoveContentForm */

$movableResult = $model->isMovable();
$canMove = $model->isMovable() === true;

?>

<?php ModalDialog::begin(['header' => Yii::t('ContentModule.base', '<strong>Move</strong> content')]) ?>
 <?php $form = ActiveForm::begin(['enableClientValidation' => false]) ?>
    <div class="modal-body">
        <?php if($canMove): ?>
              <?= $form->field($model, 'target')->widget(SpacePickerField::class, [
                      'maxSelection' => 1,
                      'focus' => true,
                      'url' => $model->getSearchUrl()
              ])?>
        <?php else: ?>
            <div class="alert alert-warning">
                <?= Yii::t('ContentModule.base', $movableResult); ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="modal-footer">
        <?= Button::primary(Yii::t('base', 'Save'))->action('content.submitMove')->submit()->loader(true)->visible($canMove) ?>
        <?= ModalButton::cancel() ?>
    </div>
 <?php ActiveForm::end() ?>
<?php ModalDialog::end() ?>
