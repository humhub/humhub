<?php

use humhub\helpers\Html;
use humhub\modules\file\converter\PreviewImage;
use humhub\modules\file\handler\BaseFileHandler;
use humhub\modules\file\widgets\FileHandlerButtonDropdown;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var $file \humhub\modules\file\models\File */
/* @var $viewHandler BaseFileHandler[] */
/* @var $editHandler BaseFileHandler[] */
/* @var $exportHandler BaseFileHandler[] */
/* @var $importHandler BaseFileHandler[] */
?>

<?php Modal::beginDialog([
    'title' => Yii::t('FileModule.base', '<strong>Open</strong> file', ['fileName' => Html::encode($file->file_name)]),
    'footer' => ModalButton::cancel(Yii::t('base', 'Close')),
]) ?>

    <?php
    $thumbnailUrl = '';
    $previewImage = new PreviewImage();
    if ($previewImage->applyFile($file)) {
        $thumbnailUrl = $previewImage->getUrl();
    }
    ?>

    <img src="<?= $thumbnailUrl; ?>" class="float-start" style="padding-right:12px">

    <h3 style="padding-top:0px;margin-top:0px"><?= Html::encode($file->file_name); ?></h3>
    <br/>

    <p style="line-height:20px">
        <strong><?= Yii::t('FileModule.base', 'Size:'); ?></strong> <?= Yii::$app->formatter->asShortSize($file->size, 1); ?>
        <br/>
        <strong><?= Yii::t('FileModule.base', 'Created by:'); ?></strong> <?= Html::encode($file->createdBy->displayName); ?>
        (<?= Yii::$app->formatter->asDatetime($file->created_at, 'short'); ?>)<br/>
        <?php if (!empty($file->updatedBy) && $file->updated_at != $file->created_at) : ?>
            <strong><?= Yii::t('FileModule.base', 'Last update by:') ?></strong> <?= Html::encode($file->updatedBy->displayName); ?> (<?= Yii::$app->formatter->asDatetime($file->updated_at, 'short'); ?>)
            <br/>
        <?php endif; ?>
    </p>

    <div class="float-start">
        <?= FileHandlerButtonDropdown::widget(['handlers' => $viewHandler]); ?>
        <?= FileHandlerButtonDropdown::widget(['handlers' => $exportHandler]); ?>
        <?= FileHandlerButtonDropdown::widget(['handlers' => array_merge($editHandler, $importHandler)]); ?>
    </div>

<?php Modal::endDialog(); ?>
