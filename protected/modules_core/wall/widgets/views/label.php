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
<?php if ($object->contentMeta->isSticked()) : ?>
    <span class="label label-danger"><?php echo Yii::t('WallModule.base', 'Sticked'); ?></span>
<?php endif; ?>

<?php if ($object->contentMeta->isArchived()) : ?>
    <span class="label label-warning"><?php echo Yii::t('WallModule.base', 'Archived'); ?></span>
<?php endif; ?>

<?php if ($object->contentMeta->isPublic()) : ?>
    <span class="label label-success"><?php echo Yii::t('WallModule.base', 'Public'); ?></span>
<?php endif; ?>

