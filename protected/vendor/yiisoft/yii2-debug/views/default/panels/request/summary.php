<?php
/* @var $panel yii\debug\panels\RequestPanel */

use yii\helpers\Html;
use yii\web\Response;

$statusCode = $panel->data['statusCode'];
if ($statusCode === null) {
    $statusCode = 200;
}
if ($statusCode >= 200 && $statusCode < 300) {
    $class = 'yii-debug-toolbar__label_success';
} elseif ($statusCode >= 300 && $statusCode < 400) {
    $class = 'yii-debug-toolbar__label_info';
} else {
    $class = 'yii-debug-toolbar__label_important';
}
$statusText = Html::encode(isset(Response::$httpStatuses[$statusCode]) ? Response::$httpStatuses[$statusCode] : '');
?>
<div class="yii-debug-toolbar__block">
    <a href="<?= $panel->getUrl() ?>" title="Status code: <?= $statusCode ?> <?= $statusText ?>">Status <span class="yii-debug-toolbar__label <?= $class ?>"><?= $statusCode ?></span></a>
    <a href="<?= $panel->getUrl() ?>" title="Action: <?= $panel->data['action'] ?>">Route <span class="yii-debug-toolbar__label"><?= $panel->data['route'] ?></span></a>
</div>
