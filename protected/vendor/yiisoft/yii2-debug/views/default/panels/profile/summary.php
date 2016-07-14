<?php
/* @var $panel yii\debug\panels\ProfilingPanel */
/* @var $time integer */
/* @var $memory integer */
?>
<div class="yii-debug-toolbar__block">
    <a href="<?= $panel->getUrl() ?>" title="Total request processing time was <?= $time ?>">Time <span class="yii-debug-toolbar__label yii-debug-toolbar__label_info"><?= $time ?></span></a>
    <a href="<?= $panel->getUrl() ?>" title="Peak memory consumption">Memory <span class="yii-debug-toolbar__label yii-debug-toolbar__label_info"><?= $memory ?></span></a>
</div>
