<?php

use humhub\modules\content\widgets\stream\WallStreamEntryWidget;
use humhub\modules\ui\icon\widgets\Icon;

/* @var $this humhub\modules\ui\view\components\View */
/* @var $mode string */

?>
<li>
    <?php if($mode === WallStreamEntryWidget::EDIT_MODE_INLINE) : ?>
            <a href="#" class="stream-entry-edit-link" data-action-click="edit" data-action-url="<?= $editUrl ?>">
                <?= Icon::get('edit')?> <?= Yii::t('ContentModule.base', 'Edit') ?>
            </a>
            <a href="#" class="stream-entry-cancel-edit-link"  data-action-click="cancelEdit" style="display:none;">
                <?= Icon::get('edit')?> <?= Yii::t('ContentModule.base', 'Cancel Edit') ?>
            </a>
    <?php elseif ($mode === WallStreamEntryWidget::EDIT_MODE_MODAL) : ?>
            <a href="#" class="stream-entry-edit-link" data-action-click="editModal" data-action-url="<?= $editUrl ?>">
                <?= Icon::get('edit')?> <?=  Yii::t('ContentModule.base', 'Edit') ?>
            </a>
    <?php elseif ($mode === WallStreamEntryWidget::EDIT_MODE_NEW_WINDOW) : ?>
            <a href="<?= $editUrl ?>" class="stream-entry-edit-link">
                <?= Icon::get('edit')?> <?=  Yii::t('ContentModule.base', 'Edit') ?>
            </a>
    <?php endif; ?>
</li>
