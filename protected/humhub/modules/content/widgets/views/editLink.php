<?php

/* @var $this humhub\components\View */
?>
<li>
    <a href="#" class="stream-entry-edit-link" data-action-click="edit" data-action-url="<?= $editUrl ?>"><i class="fa fa-pencil"></i> <?= Yii::t('ContentModule.widgets_views_editLink', 'Edit') ?></a>
    <a href="#" class="stream-entry-cancel-edit-link"  data-action-click="cancelEdit" style="display:none;"><i class="fa fa-pencil"></i> <?= Yii::t('ContentModule.widgets_views_editLink', 'Cancel Edit') ?></a>
</li>
