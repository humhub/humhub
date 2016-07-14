<?php
/* @var $panel yii\debug\panels\LogPanel */
/* @var $data array */

use yii\log\Target;
use yii\log\Logger;

?>

<?php
$title = 'Logged ' . count($data['messages']) . ' messages';
$errorCount = count(Target::filterMessages($data['messages'], Logger::LEVEL_ERROR));
$warningCount = count(Target::filterMessages($data['messages'], Logger::LEVEL_WARNING));
$output = [];

if ($errorCount) {
    $output[] = "<span class=\"yii-debug-toolbar__label yii-debug-toolbar__label_important\">$errorCount</span>";
    $title .= ", $errorCount errors";
}

if ($warningCount) {
    $output[] = "<span class=\"yii-debug-toolbar__label yii-debug-toolbar__label_warning\">$warningCount</span>";
    $title .= ", $warningCount warnings";
}
?>

<div class="yii-debug-toolbar__block">
    <a href="<?= $panel->getUrl() ?>" title="<?= $title ?>">Log
        <span class="yii-debug-toolbar__label"><?= count($data['messages']) ?></span>
        <?= implode('&nbsp;', $output) ?>
    </a>
</div>
