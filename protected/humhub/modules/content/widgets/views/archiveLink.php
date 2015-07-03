<?php
/* @var $this humhub\components\View */

use yii\helpers\Url;

$this->registerJsVar('wallArchiveLinkUrl', Url::to(['/content/content/archive', 'id' => '-id-']));
$this->registerJsVar('wallUnarchiveLinkUrl', Url::to(['/content/content/unarchive', 'id' => '-id-']));
?>
<li>
    <?php if ($object->content->isArchived()): ?>
        <a href="#" onClick="wallUnarchive('<?php echo $id; ?>');
                return false;"><i class="fa fa-archive"></i> <?php echo Yii::t('ContentModule.widgets_views_archiveLink', 'Unarchive'); ?></a>
       <?php else: ?>
        <a href="#" onClick="wallArchive('<?php echo $id; ?>');
                return false;"><i class="fa fa-archive"></i> <?php echo Yii::t('ContentModule.widgets_views_archiveLink', 'Move to archive'); ?></a>
    <?php endif; ?>
</li>
