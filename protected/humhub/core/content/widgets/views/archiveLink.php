<?php
/* @var $this humhub\components\View */

$this->registerJsVar('wallArchiveLinkUrl', Url::to(['/content/content/archive', 'className' => '-className-', 'id' => '-id-']));
$this->registerJsVar('wallUnarchiveLinkUrl', Url::to(['/content/content/unarchive', 'className' => '-className-', 'id' => '-id-']));
?>
<li>
<?php if ($object->content->isArchived()): ?>
        <a href="#" onClick="wallUnarchive('<?php echo $model; ?>', '<?php echo $id; ?>');
                    return false;"><i class="fa fa-archive"></i> <?php echo Yii::t('ContentModule.widgets_views_archiveLink', 'Unarchive'); ?></a>
       <?php else: ?>
        <a href="#" onClick="wallArchive('<?php echo $model; ?>', '<?php echo $id; ?>');
                    return false;"><i class="fa fa-archive"></i> <?php echo Yii::t('ContentModule.widgets_views_archiveLink', 'Move to archive'); ?></a>
       <?php endif; ?>
</li>
