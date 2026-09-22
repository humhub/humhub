<?php
/* @var $this \humhub\components\View */

use humhub\widgets\Icon;
use yii\helpers\Url;

$archiveLink = Url::to(['/content/content/archive', 'id' => $id]);
$unarchiveLink = Url::to(['/content/content/unarchive', 'id' => $id]);

?>
<li>
    <?php if ($object->content->isArchived()): ?>
        <a href="#"
           class="dropdown-item "
           data-action-click="unarchive"
           data-action-url="<?= $unarchiveLink ?>">
            <?= Icon::get('archive') ?> <?= Yii::t('ContentModule.base', 'Unarchive'); ?>
        </a>
    <?php else: ?>
        <a href="#"
           class="dropdown-item "
           data-action-click="archive"
           data-action-url="<?= $archiveLink ?>">
            <?= Icon::get('archive') ?> <?= Yii::t('ContentModule.base', 'Move to archive'); ?>
        </a>
    <?php endif; ?>
</li>
