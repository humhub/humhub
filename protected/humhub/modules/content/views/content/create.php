<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\content\models\forms\CreateContentForm;
use humhub\modules\mail\models\Message;
use humhub\modules\post\widgets\Form;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\SpacePickerField;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\ui\view\components\View;
use humhub\modules\user\models\User;
use humhub\widgets\ModalDialog;
use yii\bootstrap\Alert;
use yii\helpers\Url;

/**
 * @var $this View
 * @var $model CreateContentForm
 */

$targetNames = $model->getTargetNames();

$targetHeader = [
    User::class => Yii::t('SpaceModule.base', 'Create in my Profile'),
    Space::class => Yii::t('SpaceModule.base', 'Create in Space'),
];
?>
<?php ModalDialog::begin([
    'id' => 'create-content-modal',
    'header' => $targetHeader[$model->target] ?? Yii::t('ContentModule.base', 'Select a target'),
]) ?>

<?php $form = ActiveForm::begin() ?>

<?php foreach ($model->fileList as $index => $file) : ?>
    <?= Html::hiddenInput(CreateContentForm::class . "[fileList][$index]", $file) ?>
<?php endforeach; ?>

<div class="modal-body">
    <?php if (!$model->target): ?>
        <?php if (!$targetNames): ?>
            <?= Alert::widget([
                'options' => ['class' => 'alert-warning'],
                'body' => Yii::t('ContentModule.base', 'You cannot create content. Please start by joining a space.'),
                'closeButton' => false,
            ]) ?>
        <?php else: ?>
            <?= $form->field($model, 'target')->radioList($targetNames, ['data-action-change' => 'ui.modal.submit'])->label(false) ?>
        <?php endif; ?>

    <?php else: ?>
        <?= $form->field($model, 'target')->hiddenInput()->label(false) ?>

        <?php if ($model->target === User::class): ?>
            <div id="user-content-create-form" data-stream-create-content="stream.wall.WallStream">
                <?= Form::widget([
                    'contentContainer' => Yii::$app->user->identity,
                    'fileList' => $model->fileList,
                    'isModal' => true,
                ]) ?>
            </div>

        <?php elseif ($model->target === Space::class): ?>
            <?php if (!$model->targetSpace): ?>
                <?= $form->field($model, 'targetSpaceGuids')->widget(SpacePickerField::class, [
                    'maxSelection' => 1,
                    'focus' => true,
                    'url' => $model->getSpaceSearchUrl(),
                    'options' => ['data-action-change' => 'ui.modal.submit'],
                ]) ?>
            <?php else: ?>
                <div id="space-content-create-form" data-stream-create-content="stream.wall.WallStream">
                    <?= Form::widget([
                        'contentContainer' => $model->targetSpace,
                        'fileList' => $model->fileList,
                    ]) ?>
                </div>
            <?php endif; ?>

        <?php endif ?>
    <?php endif; ?>
</div>

<?php ActiveForm::end() ?>

<?php ModalDialog::end() ?>

<script <?= Html::nonce() ?>>
    $(function () {
        <?php if (
            $model->target === User::class
            || ($model->target === Space::class && $model->targetSpace)
        ): ?>

        humhub.modules.content.form.init();
        $('.contentForm_options').show();
        $('#create-content-modal').find('input[type=text], textarea, .ProseMirror').eq(0).trigger('click').focus();

        <?php elseif ($model->target === Message::class): ?>

        humhub.modules.ui.modal.global.load("<?= Url::to(['/mail/mail/create']) ?>");

        <?php endif; ?>
    });
</script>
