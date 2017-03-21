<?php
/**
 * This view shows common labels for wall entries.
 * Its used by WallEntryLabelWidget.
 *
 * @property Mixed $object the content object (e.g. Post)
 *
 * @package humhub.modules_core.wall
 * @since 0.5
 */
?>
<?php if ($object->content->isSticked()) : ?>
    <span class="label label-danger"><?= Yii::t('ContentModule.widgets_views_label', 'Sticked'); ?></span>
<?php endif; ?>

<?php if ($object->content->isArchived()) : ?>
    <span class="label label-warning"><?= Yii::t('ContentModule.widgets_views_label', 'Archived'); ?></span>
<?php endif; ?>

<?php if ($object->content->isPublic()) : ?>
    <span class="label label-success"><?= Yii::t('ContentModule.widgets_views_label', 'Public'); ?></span>
<?php endif; ?>

