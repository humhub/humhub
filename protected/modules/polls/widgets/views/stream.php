<?php
/**
 * This view shows the stream of all available polls.
 * Used by PollStreamWidget.
 *
 * @property Space $space the current space
 *
 * @package humhub.modules.polls.widgets.views
 * @since 0.5
 */
?>
<ul class="nav nav-tabs wallFilterPanel" id="filter">
    <li class=" dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo Yii::t('WallModule.base', 'Filter'); ?> <b class="caret"></b></a>
        <ul class="dropdown-menu">
            <!--<li><a href="#" class="wallFilter" id="filter_visibility_public"><i class="icon-check"></i> <?php echo Yii::t('PollsModule.base', 'Display all'); ?></a></li>-->
            <li><a href="#" class="wallFilter" id="filter_polls_notAnswered"><i class="icon-check-empty"></i> <?php echo Yii::t('PollsModule.base', 'No answered yet'); ?></a></li>
            <li><a href="#" class="wallFilter" id="filter_entry_mine"><i class="icon-check-empty"></i> <?php echo Yii::t('PollsModule.base', 'Asked by me'); ?></a></li>
            <li><a href="#" class="wallFilter" id="filter_visibility_public"><i class="icon-check-empty"></i> <?php echo Yii::t('PollsModule.base', 'Only public polls'); ?></a></li>
            <li><a href="#" class="wallFilter" id="filter_visibility_private"><i class="icon-check-empty"></i> <?php echo Yii::t('PollsModule.base', 'Only private polls'); ?></a></li>
        </ul>
    </li>
    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo Yii::t('WallModule.base', 'Sorting'); ?> <b class="caret"></b></a>
        <ul class="dropdown-menu">
            <li><a href="#" class="wallSorting" id="sorting_c"><i class="icon-check"></i> <?php echo Yii::t('WallModule.base', 'Creation time'); ?></a></li>
            <li><a href="#" class="wallSorting" id="sorting_u"><i class="icon-check-empty"></i> <?php echo Yii::t('WallModule.base', 'Last update'); ?></a></li>
        </ul>
    </li>
</ul>

<div id="pollStream">

    <!-- DIV for an normal wall stream -->
    <div class="s2_stream" style="display:none">
        <div class="s2_streamContent"></div>
        <div class="loader streamLoader"></div>
        <div class="emptyStreamMessage">
            <div class="placeholder">
                <b><?php echo Yii::t('PollsModule.base', 'There are no polls yet!'); ?></b>
            </div>
        </div>
        <div class="emptyFilterStreamMessage">
            <div class="placeholder">
                <b><?php echo Yii::t('PollsModule.base', 'No poll found which matches your current filter(s)!'); ?></b>
            </div>
        </div>
    </div>

    <!-- DIV for an single wall entry -->
    <div class="s2_single">
        <div class="back_button_holder">
            <a href="#" class="singleBackLink button_white"><?php echo Yii::t('WallModule.base', 'Back to stream'); ?></a>
        </div>
        <div class="p_border"></div>

        <div class="s2_singleContent"></div>
        <div class="loader streamLoaderSingle"></div>
    </div>
</div>


<script>
    // Kill current stream
    if (currentStream) {
        currentStream.clear();
    }

    s = new Stream("#pollStream", "<?php echo $startUrl; ?>", "<?php echo $reloadUrl; ?>", "<?php echo $singleEntryUrl; ?>");
    s.showStream();
    currentStream = s;

</script>


