<div class="pull-right">
    <?php
    if ($previousUrl != "") {
        echo HHtml::ajaxLink("&laquo; " . Yii::t('CommentModule.widgets_views_pagination', "Previous"), $previousUrl, array(
            'success' => "function(html) { $('#comments_area_" . $id . "').html(html); }",
                ), array('id' => $id . "_pagePrevLink"));
    }

    if ($nextUrl != "" && $previousUrl != "")
        echo " &middot; ";
    ?>
    <?php
    if ($nextUrl != "") {
        echo HHtml::ajaxLink(Yii::t('CommentModule.widgets_views_pagination', "Next") . " &raquo;", $nextUrl, array(
            'success' => "function(html) { console.log(html); $('#comments_area_" . $id . "').html(html); }",
                ), array('id' => $id . "_pageNextLink"));
    }
    ?>
</div>
<?php echo Yii::t('CommentModule.widgets_views_pagination', "Show comments %from% - %to% of total %total%", array('%to%' => $showTo, "%from%" => $showFrom, "%total%" => $showTotal)); ?>
<hr>