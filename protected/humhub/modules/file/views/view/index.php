<?php

use humhub\libs\Html;
use humhub\widgets\ModalDialog;
use humhub\modules\file\converter\PreviewImage;
use humhub\modules\file\widgets\FileHandlerButtonDropdown;
?>

    <?php ModalDialog::begin(['header' => Yii::t('FileModule.base', '<strong>Open</strong> file', ['fileName' => Html::encode($file->file_name)])]) ?>
<div class="modal-body">
    
    <?php
    $thumbnailUrl = '';
    $previewImage = new PreviewImage();
    if ($previewImage->applyFile($file)) {
        $thumbnailUrl = $previewImage->getUrl();
    }
    ?>

    <img src="<?= $thumbnailUrl; ?>" class="pull-left" style="padding-right:12px">

    <h3 style="padding-top:0px;margin-top:0px"><?= Html::encode($file->file_name); ?></h3>
    <br />

    <p style="line-height:20px">
        <strong>Size:</strong> <?= Yii::$app->formatter->asShortSize($file->size, 1); ?><br />
        <strong>Created by:</strong> <?= $file->createdBy->displayName; ?> (<?= Yii::$app->formatter->asDatetime($file->created_at, 'short'); ?>)<br />
        <?php if (!empty($file->updated_at) && $file->updated_at != $file->created_at) : ?>
            <strong>Last update by:</strong> <?= $file->updatedBy->displayName; ?> (<?= Yii::$app->formatter->asDatetime($file->updated_at, 'short'); ?>)<br/>
        <?php endif; ?>
    </p>

    <div class="clearfix"></div>
</div>

<div class="modal-footer">

    <hr />
    <div class="pull-left">
        <?= FileHandlerButtonDropdown::widget(['handlers' => array_merge($viewHandler, $exportHandler)]); ?>
        <?= FileHandlerButtonDropdown::widget(['handlers' => array_merge($editHandler, $importHandler)]); ?>
    </div>

    <a href="#" data-dismiss="modal" class="btn btn-default pull-right"  data-ui-loader>Close</a>
</div>

<?php ModalDialog::end(); ?>