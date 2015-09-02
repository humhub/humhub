<?php
/**
 * This view shows the archive link for wall entries.
 * Its used by ArchiveLinkWidget.
 *
 * @property Object $object the content object (e.g. Post)
 * @property String $model the model name (e.g. Post)
 * @property String $id the primary key of the model (e.g. 1)
 *
 * @package humhub.modules_core.wall
 * @since 0.5
 */
?>
<?php if ($object->content->canArchive()) : ?>
    <li>
    <?php if ($object->content->isArchived()): ?>
        <a href="#" onClick="wallUnarchive('<?php echo $model; ?>', '<?php echo $id; ?>');
                        return false;"><i class="fa fa-archive"></i> <?php echo Yii::t('WallModule.widgets_views_archiveLink', 'Unarchive'); ?></a>
       <?php else: ?>
        <a href="#" onClick="wallArchive('<?php echo $model; ?>', '<?php echo $id; ?>');
                        return false;"><i class="fa fa-archive"></i> <?php echo Yii::t('WallModule.widgets_views_archiveLink', 'Move to archive'); ?></a>
    <?php endif; ?>
    </li>
<?php endif; ?>