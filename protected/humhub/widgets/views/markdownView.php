<?php
$this->registerJsFile('@web/resources/highlight.js/highlight.pack.js', ['position' => yii\web\View::POS_BEGIN]);
$this->registerCssFile('@web/resources/highlight.js/styles/' . $highlightJsCss . '.css');
?>
<div class="markdown-render">
    <?php echo $content; ?>
</div>

<script>
    $(function () {
        $("pre code").each(function (i, e) {
            hljs.highlightBlock(e);
        });
    });
</script>