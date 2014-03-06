<ul class="nav nav-tabs wallFilterPanel" id="filter">
    <li class=" dropdown"> <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo Yii::t('WallModule.base', 'Filter'); ?> <b class="caret"></b></a>
        <ul class="dropdown-menu">
            <li><a href="#" class="wallFilter" id="filter_tasks_meAssigned"><i class="icon-check-empty"></i> <?php echo Yii::t('TasksModule.base', 'Assigned to me'); ?></a></li>
            <li><a href="#" class="wallFilter" id="filter_entry_mine"><i class="icon-check-empty"></i> <?php echo Yii::t('TasksModule.base', 'Created by me'); ?></a></li>
            <li><a href="#" class="wallFilter" id="filter_tasks_open"><i class="icon-check-empty"></i> <?php echo Yii::t('TasksModule.base', 'State is open'); ?></a></li>
            <li><a href="#" class="wallFilter" id="filter_tasks_finished"><i class="icon-check-empty"></i> <?php echo Yii::t('TasksModule.base', 'State is finished'); ?></a></li>
            <li><a href="#" class="wallFilter" id="filter_tasks_notassigned"><i class="icon-check-empty"></i> <?php echo Yii::t('TasksModule.base', 'Nobody assigned'); ?></a></li>
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


<div id="taskStream">

    <!-- DIV for an normal wall stream -->
    <div class="s2_stream" style="display:none">

        <div class="s2_streamContent"></div>
        <div class="loader streamLoader"></div>

        <div class="emptyStreamMessage">
            <div class="placeholder">
                <b><?php echo Yii::t('TasksModule.base', 'There are no tasks yet!'); ?></b> 
            </div>
        </div>

        <div class="emptyFilterStreamMessage">
            <div class="placeholder">
                <b><?php echo Yii::t('TasksModule.base', 'No tasks found which matches your current filter(s)!'); ?></b> 
            </div>
        </div>

    </div>

    <!-- DIV for an single wall entry -->
    <div class="s2_single">
        <a href="#" class="singleBackLink"><?php echo Yii::t('WallModule.base', 'Back to stream'); ?></a>

        <div class="s2_singleContent"></div>
        <div class="loader streamLoaderSingle"></div>

        <a href="#" class="singleBackLink"><?php echo Yii::t('WallModule.base', 'Back to stream'); ?></a>
    </div>
</div>


<script>
    // Kill current stream
    if (currentStream) {
        currentStream.clear();
    }

    s = new Stream("#taskStream", "<?php echo $startUrl; ?>", "<?php echo $reloadUrl; ?>", "<?php echo $singleEntryUrl; ?>");
    s.showStream();
    currentStream = s;

</script>


