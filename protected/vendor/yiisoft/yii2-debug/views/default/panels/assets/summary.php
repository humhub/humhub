<?php
/* @var $panel yii\debug\panels\AssetPanel */
if (!empty($panel->data)):
?>
<div class="yii-debug-toolbar__block">
    <a href="<?= $panel->getUrl() ?>" title="Number of asset bundles loaded">Asset Bundles <span class="yii-debug-toolbar__label yii-debug-toolbar__label_info"><?= count($panel->data) ?></span></a>
</div>
<?php endif; ?>
