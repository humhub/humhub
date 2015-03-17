<div class="showMore">
    <?php
    echo HHtml::ajaxLink(Yii::t('CommentModule.widgets_views_pagination', "Show %count% more comments", array('%count%' => $moreCount)), $showMoreUrl, array(
        'success' => "function(html) { $('#comments_area_" . $id . "').find('.showMore').hide(); $('#comments_area_" . $id . "').prepend(html);    }",
            ), array('id' => $id . "_pagePrevLink"));
    ?>
    <hr />
</div>
