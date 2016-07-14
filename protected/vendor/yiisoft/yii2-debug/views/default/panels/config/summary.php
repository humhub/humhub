<?php
/* @var $panel yii\debug\panels\ConfigPanel */
?>
<div class="yii-debug-toolbar__block">
    <a href="<?= $panel->getUrl() ?>">
        <span class="yii-debug-toolbar__label"><?= $panel->data['application']['yii'] ?></span>
        PHP
        <span class="yii-debug-toolbar__label"><?= $panel->data['php']['version'] ?></span>
    </a>
</div>
