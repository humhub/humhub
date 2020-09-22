<?php
/* @var $this humhub\components\View */

use yii\helpers\Url;

$archiveLink = Url::to(['/content/content/archive', 'id' => $id]);
$unarchiveLink = Url::to(['/content/content/unarchive', 'id' => $id]);

?>
<li>
    <?php if ($object->content->isArchived()): ?>
        <a href="#" data-action-click="unarchive" data-action-url="<?= $unarchiveLink ?>">
            <i class="fa fa-archive"></i> <?= Yii::t('ContentModule.base', 'Unarchive'); ?>
        </a>
    <?php else: ?>
        <a href="#" data-action-click="archive" data-action-url="<?= $archiveLink ?>">
            <i class="fa fa-archive"></i> <?= Yii::t('ContentModule.base', 'Move to archive'); ?>
        </a>
    <?php endif; ?>
</li>
