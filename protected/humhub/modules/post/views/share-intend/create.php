<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\post\widgets\Form;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;
use yii\helpers\StringHelper;

/**
 * @var $this View
 * @var $fileList array
 * @var $shareTarget ContentContainerActiveRecord
 */
?>

<?php $form = Modal::beginFormDialog([
    'id' => 'share-intend-modal',
    'title' => Yii::t('FileModule.base', 'Share in {targetDisplayName}', [
        'targetDisplayName' => $shareTarget->guid === Yii::$app->user->identity->guid ?
            Yii::t('base', 'My Profile') :
            Html::encode(StringHelper::truncate($shareTarget->displayName, 10)),
    ]),
    'footer' => ModalButton::light(Yii::t('base', 'Back'))
        ->load(['/post/share-intend']),
]) ?>

    <div id="space-content-create-form" data-stream-create-content="stream.wall.WallStream">
        <?= Form::widget([
            'contentContainer' => $shareTarget,
            'fileList' => $fileList,
            'isModal' => true,
        ]) ?>
    </div>

<?php Modal::endFormDialog() ?>

<script <?= Html::nonce() ?>>
    $(function () {
        humhub.modules.content.form.initModal();
        $('#share-intend-modal').find('input[type=text], textarea, .ProseMirror').eq(0).trigger('click').focus();
    });
</script>
