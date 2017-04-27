<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

$this->registerJsFile('@web-static/resources/js/highlight.js/highlight.pack.js', ['position' => yii\web\View::POS_BEGIN]);
$this->registerCssFile('@web-static/resources/js/highlight.js/styles/' . $highlightJsCss . '.css');
?>
<div class="markdown-render">
    <?= $content; ?>
</div>

<script>
    $(function () {
        $("pre code").each(function (i, e) {
            hljs.highlightBlock(e);
        });
    });
</script>