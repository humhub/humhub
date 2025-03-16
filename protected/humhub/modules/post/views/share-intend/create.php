<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\post\widgets\Form;
use humhub\modules\ui\view\components\View;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\ModalDialog;

/**
 * @var $this View
 * @var $fileList array
 * @var $shareTarget \humhub\modules\content\components\ContentContainerActiveRecord
 */
?>

<?php ModalDialog::begin([
    'id' => 'create-content-modal',
    'header' => Yii::t('FileModule.base', 'Share the file with a Post')
    ]) ?>
<?php $form = ActiveForm::begin() ?>

<div class="modal-body">
    <div id="space-content-create-form" data-stream-create-content="stream.wall.WallStream">
        <?= Form::widget([
            'contentContainer' => $shareTarget,
            'fileList' => $fileList,
            'isModal' => true,
        ]) ?>
    </div>
</div>

<?php ActiveForm::end() ?>
<?php ModalDialog::end() ?>

<script <?= Html::nonce() ?>>
    $(function () {
        humhub.modules.content.form.init();
        $('.contentForm_options').show();

        // ToDo: Fix  me - Init seems not to work
        $('.contentFormBody').show();

        $('#create-content-modal').find('input[type=text], textarea, .ProseMirror').eq(0).trigger('click').focus();
    });
</script>
