<?php

use humhub\modules\content\widgets\WallEntry;

/* @var $this humhub\components\View */

?>
<li>
    <?php if($mode === WallEntry::EDIT_MODE_INLINE) : ?>
            <a href="#" class="stream-entry-edit-link" data-action-click="edit" data-action-url="<?= $editUrl ?>">
                <i class="fa fa-pencil"></i> <?= Yii::t('ContentModule.base', 'Edit') ?>
            </a>
            <a href="#" class="stream-entry-cancel-edit-link"  data-action-click="cancelEdit" style="display:none;">
                <i class="fa fa-pencil"></i> <?= Yii::t('ContentModule.base', 'Cancel Edit') ?>
            </a>
    <?php elseif ($mode === WallEntry::EDIT_MODE_MODAL) : ?>
            <a href="#" class="stream-entry-edit-link" data-action-click="editModal" data-action-url="<?= $editUrl ?>">
                <i class="fa fa-pencil"></i><?=  Yii::t('ContentModule.base', 'Edit') ?>
            </a>
    <?php elseif ($mode === WallEntry::EDIT_MODE_NEW_WINDOW) : ?>
            <a href="<?= $editUrl ?>" class="stream-entry-edit-link">
                <i class="fa fa-pencil"></i><?=  Yii::t('ContentModule.base', 'Edit') ?>
            </a>
    <?php endif; ?>
</li>
