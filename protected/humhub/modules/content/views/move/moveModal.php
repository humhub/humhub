<?php

use humhub\modules\content\models\forms\MoveContentForm;
use humhub\modules\space\widgets\SpacePickerField;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var $model MoveContentForm */

$movableResult = $model->isMovable();
$canMove = $model->isMovable() === true;

?>

<?php $form = Modal::beginFormDialog([
    'title' => Yii::t('ContentModule.base', '<strong>Move</strong> content'),
    'footer' => ModalButton::cancel() . ' ' . Button::primary(Yii::t('base', 'Save'))->action('content.submitMove')->submit()->loader(true)->visible($canMove),
    'form' => ['enableClientValidation' => false],
]) ?>

    <?php if ($canMove): ?>
        <?= $form->field($model, 'target')->widget(SpacePickerField::class, [
            'maxSelection' => 1,
            'focus' => true,
            'url' => $model->getSearchUrl(),
        ]) ?>
    <?php else: ?>
        <div class="alert alert-warning">
            <?= Yii::t('ContentModule.base', $movableResult); ?>
        </div>
    <?php endif; ?>

<?php Modal::endFormDialog(); ?>
