<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\assets\BootstrapMarkdownAsset;
use yii\helpers\Url;
use yii\helpers\Html;

/**
 * Register BootstrapMarkdown & changes
 */
BootstrapMarkdownAsset::register($this);
$this->registerCssFile('@web-static/css/bootstrap-markdown-override.css');
$this->registerJsFile('@web-static/js/markdownEditor.js');

/**
 * Create a hidden field to store uploaded files guids
 */
echo Html::hiddenInput('fileUploaderHiddenGuidField', "", ['id' => 'fileUploaderHiddenGuidField_' . $fieldId]);

$this->registerJsVar('markdownPreviewUrl', $previewUrl);

$translations = [
    'Bold' => Yii::t('widgets_views_markdownEditor', 'Bold'),
    'Italic' => Yii::t('widgets_views_markdownEditor', 'Italic'),
    'Heading' => Yii::t('widgets_views_markdownEditor', 'Heading'),
    'URL/Link' => Yii::t('widgets_views_markdownEditor', 'URL/Link'),
    'Image/File' => Yii::t('widgets_views_markdownEditor', 'Image/File'),
    'Image' => Yii::t('widgets_views_markdownEditor', 'Image'),
    'List' => Yii::t('widgets_views_markdownEditor', 'List'),
    'Preview' => Yii::t('widgets_views_markdownEditor', 'Preview'),
    'strong text' => Yii::t('widgets_views_markdownEditor', 'strong text'),
    'emphasized text' => Yii::t('widgets_views_markdownEditor', 'emphasized text'),
    'heading text' => Yii::t('widgets_views_markdownEditor', 'heading text'),
    'enter link description here' => Yii::t('widgets_views_markdownEditor', 'enter link description here'),
    'Insert Hyperlink' => Yii::t('widgets_views_markdownEditor', 'Insert Hyperlink'),
    'enter image description here' => Yii::t('widgets_views_markdownEditor', 'enter image description here'),
    'Insert Image Hyperlink' => Yii::t('widgets_views_markdownEditor', 'Insert Image Hyperlink'),
    'enter image title here' => Yii::t('widgets_views_markdownEditor', 'enter image title here'),
    'list text here' => Yii::t('widgets_views_markdownEditor', 'list text here'),
    'Quote' => Yii::t('widgets_views_markdownEditor', 'Quote'),
    'quote here' => Yii::t('widgets_views_markdownEditor', 'quote here'),
    'Code' => Yii::t('widgets_views_markdownEditor', 'Code'),
    'code text here' => Yii::t('widgets_views_markdownEditor', 'code text here'),
    'Unordered List' => Yii::t('widgets_views_markdownEditor', 'Unordered List'),
    'Ordered List' => Yii::t('widgets_views_markdownEditor', 'Ordered List'),
];

$translationsJS = "$.fn.markdown.messages['en'] = {\n";
foreach ($translations as $key => $value) {
    $translationsJS .= "\t'" . $key . "': '" . Html::encode($value) . "',\n";
}
$translationsJS .= "};\n";
$this->registerJs($translationsJS);
$this->registerJs("initMarkdownEditor('" . $fieldId . "')");

?>

<?php
/**
 * We need to use this script part since a markdown editor can also included
 * into a modal. So we need to append MarkdownEditors modals later to body.
 */
?>
<script id="markdownEditor_dialogs_<?= $fieldId; ?>" type="text/placeholder">
    <div class="modal modal-top" id="addFileModal_<?= $fieldId; ?>" tabindex="-1" role="dialog"
         aria-labelledby="addImageModalLabel" style="z-index:99999" aria-hidden="true">
    <div class="modal-dialog">
    <div class="modal-content">
    <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <h4 class="modal-title" id="addImageModalLabel">
        <?= Yii::t('widgets_views_markdownEditor', 'Add image/file'); ?>
    </h4>
    </div>
    <div class="modal-body">

    <div class="uploadForm">
    <?= Html::beginForm('', 'post'); ?>
    <input class="fileUploadButton" type="file"
    name="files[]"
    data-url="<?= Url::to(['/file/file/upload']); ?>"
    multiple>
    <?= Html::endForm(); ?>
    </div>

    <div class="uploadProgress">
    <strong><?= Yii::t('widgets_views_markdownEditor', 'Please wait while uploading...'); ?></strong>
    </div>


    </div>
    <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">
    <?= Yii::t('widgets_views_markdownEditor', 'Close'); ?>
    </button>
    </div>
    </div>
    </div>
    </div>

    <div class="modal modal-top" id="addLinkModal_<?= $fieldId; ?>" tabindex="-1" role="dialog" style="z-index:99999"
	aria-labelledby="addLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog">
    <div class="modal-content">
    <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
    </button>
    <h4 class="modal-title" id="addLinkModalLabel"><?= Yii::t('widgets_views_markdownEditor', 'Add link'); ?></h4>
    </div>
    <div class="modal-body">
    <div class="form-group">
    <label for="addLinkTitle"><?= Yii::t('widgets_views_markdownEditor', 'Title'); ?></label>
    <input type="text" class="form-control linkTitle"
    placeholder="<?= Yii::t('widgets_views_markdownEditor', 'Title of your link'); ?>">
    </div>
    <div class="form-group">
    <label for="addLinkTarget"><?= Yii::t('widgets_views_markdownEditor', 'Target'); ?></label>
    <input type="text" class="form-control linkTarget"
    placeholder="<?= Yii::t('widgets_views_markdownEditor', 'Enter a url (e.g. http://example.com)'); ?>">
    </div>
    </div>
    <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">
    <?= Yii::t('widgets_views_markdownEditor', 'Close'); ?>
    </button>
    <button type="button" class="btn btn-primary addLinkButton">
    <?= Yii::t('widgets_views_markdownEditor', 'Add link'); ?>
    </button>
    </div>
    </div>
    </div>
    </div>
</script>
