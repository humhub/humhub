<?php


use yii\web\JsExpression;

/* @var $this humhub\components\View */
?>
<li>
    <?php
    echo \humhub\widgets\AjaxButton::widget([
        'label' => '<i class="fa fa-pencil"></i> ' . Yii::t('ContentModule.widgets_views_editLink', 'Edit'),
        'tag' => 'a',
        'ajaxOptions' => [
            'type' => 'POST',
            'success' => new JsExpression('function(html){ $(".preferences .dropdown").removeClass("open"); $("#wall_content_' . $content->getUniqueId() . '").replaceWith(html); }'),
            'url' => $editUrl,
        ],
        'htmlOptions' => [
            'href' => '#'
        ]
    ]);
    ?>
</li>
