<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\post\widgets\Form;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\ui\view\components\View;
use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;
use yii\helpers\StringHelper;

/**
 * @var $this View
 * @var $fileList array
 * @var $shareTarget ContentContainerActiveRecord
 */
?>

<?php ModalDialog::begin([
    'id' => 'share-intend-modal',
    'header' => Yii::t('FileModule.base', 'Share in {targetDisplayName}', [
        'targetDisplayName' => $shareTarget->guid === Yii::$app->user->identity->guid ?
            Yii::t('base', 'My Profile') :
            Html::encode(StringHelper::truncate($shareTarget->displayName, 10)),
    ]),
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

<div class="modal-footer">
    <?= ModalButton::defaultType(Yii::t('base', 'Back'))
        ->load(['/post/share-intend']) ?>
</div>

<?php ActiveForm::end() ?>
<?php ModalDialog::end() ?>

<script <?= Html::nonce() ?>>
    $(function () {
        humhub.modules.content.form.initModal();
        $('#share-intend-modal').find('input[type=text], textarea, .ProseMirror').eq(0).trigger('click').focus();
    });
</script>
