<div class="showMore">
    <?php
    echo humhub\widgets\AjaxButton::widget([
        'label' => Yii::t('CommentModule.widgets_views_pagination', "Show %count% more comments", array('%count%' => $moreCount)),
        'ajaxOptions' => [
            'type' => 'POST',
            'success' => new yii\web\JsExpression("function(html) { $('#comments_area_" . $id . "').find('.showMore').hide(); $('#comments_area_" . $id . "').prepend(html);    }"),
            'url' => $showMoreUrl,
        ],
        'htmlOptions' => [
            'id' => $id . "_pagePrevLink",
        ],
        'tag' => 'a'
    ]);
    ?>
    <hr />
</div>
