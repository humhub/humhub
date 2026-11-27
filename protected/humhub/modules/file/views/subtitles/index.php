<?php

use humhub\helpers\Html;
use humhub\modules\file\assets\FileVueAsset;
use humhub\modules\file\models\File;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;
use humhub\widgets\VueComponent;

/* @var $file File */
/* @var $languages array<int, array{code: string, name: string}> */
/* @var $uiLanguage string */
?>

<?php Modal::beginDialog([
    'title' => Yii::t('FileModule.base', 'Subtitles of {fileName}', ['fileName' => Html::encode($file->file_name)]),
    'footer' => ModalButton::cancel(Yii::t('base', 'Close')),
]) ?>

    <?= VueComponent::widget([
        'name' => 'SubtitleManager',
        'assetBundle' => FileVueAsset::class,
        'props' => [
            'fileId' => (int)$file->id,
            'fileName' => $file->file_name,
            'languages' => $languages,
            'uiLanguage' => $uiLanguage,
        ],
    ]) ?>

<?php Modal::endDialog(); ?>
