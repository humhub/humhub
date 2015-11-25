<?php
/* @var $this humhub\components\View */


$this->registerJsFile('@web/resources/activity/activies.js');
$this->registerJsVar('activityStreamUrl', $streamUrl);
$this->registerJsVar('activityInfoUrl', $infoUrl);
?>

<div class="panel panel-default panel-activities">

    <div
        class="panel-heading"><?php echo Yii::t('ActivityModule.widgets_views_activityStream', '<strong>Latest</strong> activities'); ?></div>
    <div id="activityStream">
        <div id="activityEmpty" style="display:none">
            <div
                class="placeholder"><?php echo Yii::t('ActivityModule.widgets_views_activityStream', 'There are no activities yet.'); ?></div>
        </div>
        <ul id="activityContents" class="media-list activities">
            <li id="activityLoader">
                <?php echo \humhub\widgets\LoaderWidget::widget(); ?>
            </li>
        </ul>

    </div>
</div>

<script type="text/javascript">

    // set niceScroll to activity list
    $("#activityContents").niceScroll({
        cursorwidth: "7",
        cursorborder: "",
        cursorcolor: "#555",
        cursoropacitymax: "0.2",
        railpadding: {top: 0, right: 3, left: 0, bottom: 0}
    });

    // update nicescroll object with new content height after ajax request
    $(document).ajaxComplete(function (event, xhr, settings) {
        $("#activityContents").getNiceScroll().resize();
    })

</script>


