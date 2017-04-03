<?php

use humhub\modules\post\models\Post;

/**
 * This view shows common labels for wall entries.
 * Its used by WallEntryLabelWidget.
 *
 * @property Mixed $object the content object (e.g. Post)
 *
 * @since 0.5
 */
?>
<span class="wallentry-labels">
    <?php if ($object->content->isPinned()) : ?>
        <span class="label label-danger"><?= Yii::t('ContentModule.widgets_views_label', 'Pinned'); ?></span>
    <?php endif; ?>

    <?php if ($object->content->isArchived()) : ?>
        <span class="label label-warning"><?= Yii::t('ContentModule.widgets_views_label', 'Archived'); ?></span>
    <?php endif; ?>

    <?php if ($object->content->isPublic()) : ?>
        <span class="label label-success"><?= Yii::t('ContentModule.widgets_views_label', 'Public'); ?></span>
    <?php endif; ?>

    <?php if (!$object instanceof Post) : ?>
        <span class="label label-default"><?= $object->getContentName(); ?></span>
    <?php endif; ?>
<span>