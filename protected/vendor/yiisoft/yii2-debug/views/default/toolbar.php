<?php
/* @var $this \yii\web\View */
/* @var $panels \yii\debug\Panel[] */
/* @var $tag string */
/* @var $position string */

use yii\helpers\Url;

$firstPanel = reset($panels);
$url = $firstPanel->getUrl();

?>
<div id="yii-debug-toolbar" class="yii-debug-toolbar yii-debug-toolbar_position_<?= $position ?>">
    <div class="yii-debug-toolbar__bar">
        <div class="yii-debug-toolbar__block yii-debug-toolbar__title">
            <a href="<?= Url::to(['index']) ?>">
                <img width="29" height="30" alt="" src="<?= \yii\debug\Module::getYiiLogo() ?>">
            </a>
        </div>

        <?php foreach ($panels as $panel): ?>
            <?= $panel->getSummary() ?>
        <?php endforeach; ?>

        <a class="yii-debug-toolbar__external" href="#" target="_blank">
            <span class="yii-debug-toolbar__external-icon"></span>
        </a>

        <span class="yii-debug-toolbar__toggle">
            <span class="yii-debug-toolbar__toggle-icon"></span>
        </span>
    </div>

    <div class="yii-debug-toolbar__view">
        <iframe src="about:blank" frameborder="0"></iframe>
    </div>
</div>
