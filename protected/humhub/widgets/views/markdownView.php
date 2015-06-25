<?php
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/resources/highlight.js/highlight.pack.js');
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/resources/highlight.js/styles/' . $this->highlightJsCss . '.css');
Yii::app()->clientScript->registerScript("highlightJs", '$("pre code").each(function(i, e) { hljs.highlightBlock(e); });');
?>
<div class="markdown-render">
<?php echo $content; ?>
</div>
