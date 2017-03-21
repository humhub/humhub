<?php

use yii\helpers\Html;
?>

<?= Html::textArea("message", '', array('id' => 'contentForm_message', 'class' => 'form-control autosize contentForm', 'rows' => '1', 'placeholder' => Yii::t("PostModule.widgets_views_postForm", "What's on your mind?"))); ?>

<!-- Modify textarea for mention input -->
<?= \humhub\widgets\RichTextEditor::widget(array(
    'id' => 'contentForm_message',
));
?>