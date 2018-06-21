<?php

use humhub\modules\space\widgets\Sidebar;
use humhub\modules\space\modules\manage\widgets\PendingApprovals;
use humhub\modules\space\widgets\Members;
use humhub\modules\activity\widgets\ActivityStreamViewer;

?>

<div class="row space-content">
    <div class="col-md-8 layout-content-container">
        <?= $content; ?>
    </div>
    <div class="col-md-4 layout-sidebar-container">
        <?= Sidebar::widget(['space' => $space, 'widgets' => [
                [ActivityStreamViewer::className(), ['contentContainer' => $space], ['sortOrder' => 10]],
                [PendingApprovals::className(), ['space' => $space], ['sortOrder' => 20]],
                [Members::className(), ['space' => $space], ['sortOrder' => 30]]
        ]]);
        ?>
    </div>
</div>
