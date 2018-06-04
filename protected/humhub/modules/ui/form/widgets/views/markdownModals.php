<?php
use humhub\modules\file\widgets\UploadButton;
use humhub\widgets\Button;
use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;

?>

<div class="modal modal-top" id="markdown-modal-file-upload" tabindex="-1" role="dialog" aria-labelledby="addImageModalLabel" style="z-index:99999" aria-hidden="true">
    <?php ModalDialog::begin(['header' => Yii::t('widgets_views_markdownEditor', 'Add image/file')])?>
        <div class="modal-body">

            <div class="uploadForm">
                <?= UploadButton::widget([
                    'id' => 'markdown-file-upload',
                    'label' => true,
                    'tooltip' => false,
                    'progress' => '#markdown-modal-upload-progress',
                    'cssButtonClass' => 'btn-default btn-sm',
                    'dropZone' => '#markdown-modal-file-upload',
                    'hideInStream' => true
                ]) ?>
            </div>

            <br>

            <div id="markdown-modal-upload-progress" style="display:none"></div>

        </div>
        <div class="modal-footer">
            <?= ModalButton::cancel(Yii::t('base', 'Close')) ?>
        </div>
    <?php ModalDialog::end() ?>
</div>

<div class="modal modal-top" id="markdown-modal-add-link" tabindex="-1" role="dialog" style="z-index:99999" aria-labelledby="addLinkModalLabel" aria-hidden="true">
    <?php ModalDialog::begin(['header' => Yii::t('widgets_views_markdownEditor', 'Add link')])?>
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
            <?= ModalButton::cancel(Yii::t('base', 'Close')) ?>
            <?= Button::primary(Yii::t('widgets_views_markdownEditor', 'Add link'))->cssClass('addLinkButton')->loader(false) ?>
        </div>
    <?php ModalDialog::end() ?>
</div>