<?php
/**
 * This view shows the streaming wall by WallStreamWidget.
 *
 * @property String $type of the wall (dashboard, space, user)
 * @property String $reloadUrl is the url to load more entries
 * @property String $startUrl is the url to load the first entries
 * @property String $singleEntryUrl is the url to load a single entry
 * @property Boolean $readonly is a flag which marks the stream as readonly. (e.g. for archived spaces)
 *
 * @package humhub.modules_core.wall
 * @since 0.5
 */
?>
<ul class="nav nav-tabs wallFilterPanel" id="filter">
    <li class=" dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo Yii::t('WallModule.base', 'Filter'); ?> <b
                class="caret"></b></a>
        <ul class="dropdown-menu">
            <!--<li><a href="#"><i class="icon-check"></i> <?php echo Yii::t('WallModule.base', 'Show all'); ?></a></li>-->

            <li><a href="#" class="wallFilter" id="filter_entry_userinvoled"><i class="icon-check-empty"></i> <?php echo Yii::t('WallModule.base', 'Where I´m involved'); ?></a></li>
            <li><a href="#" class="wallFilter" id="filter_entry_mine"><i class="icon-check-empty"></i> <?php echo Yii::t('WallModule.base', 'Created by me'); ?></a></li>

            <!-- post module related -->
            <li><a href="#" class="wallFilter" id="filter_entry_files"><i class="icon-check-empty"></i> <?php echo Yii::t('WallModule.base', 'Content with attached files'); ?></a></li>
            <li><a href="#" class="wallFilter" id="filter_posts_links"><i class="icon-check-empty"></i> <?php echo Yii::t('WallModule.base', 'Posts with links'); ?></a></li>
            <li><a href="#" class="wallFilter" id="filter_model_posts"><i class="icon-check-empty"></i> <?php echo Yii::t('WallModule.base', 'Posts only'); ?></a></li>
            <!-- /post module related -->

            <li class="divider"></li>

            <li><a href="#" class="wallFilter" id="filter_entry_archived"><i class="icon-check-empty"></i> <?php echo Yii::t('WallModule.base', 'Include archived posts'); ?></a></li>
            <li><a href="#" class="wallFilter" id="filter_visibility_public"><i class="icon-check-empty"></i> <?php echo Yii::t('WallModule.base', 'Only public posts'); ?></a></li>
            <li><a href="#" class="wallFilter" id="filter_visibility_private"><i class="icon-check-empty"></i> <?php echo Yii::t('WallModule.base', 'Only private posts'); ?></a></li>
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


<div id="wallStream">

    <!-- DIV for an normal wall stream -->
    <div class="s2_stream" style="display:none">

        <div class="s2_streamContent"></div>
        <div class="loader streamLoader"></div>

        <div class="emptyStreamMessage">
            <?php if ($type == Wall::TYPE_DASHBOARD): ?>
                <div class="placeholder">
                    <?php echo Yii::t('WallModule.base', '<b>Your dashboard is empty! - Fill it!</b><br>Just join or follow some workspaces or users!'); ?>
                </div>
            <?php elseif ($type == Wall::TYPE_USER): ?>
                <div class="placeholder placeholder-empty-stream">
                    <?php echo Yii::t('WallModule.base', '<b>There is still nothing happens</b><br>Be the first who post something...'); ?>
                </div>
            <?php elseif ($type == Wall::TYPE_SPACE): ?>
                <div class="placeholder placeholder-empty-stream">
                    <?php echo Yii::t('WallModule.base', '<b>This space is still empty!</b><br>Make the beginning and post something...'); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="emptyFilterStreamMessage">
            <div class="placeholder">
                <b><?php echo Yii::t('WallModule.base', 'Nothing found which matches your current filter(s)!'); ?></b>
            </div>
        </div>

    </div>

    <!-- DIV for an single wall entry -->
    <div class="s2_single">
        <div class="back_button_holder">
            <a href="#" class="singleBackLink btn btn-primary"><?php echo Yii::t('WallModule.base', 'Back to stream'); ?></a><br><br>
        </div>
        <div class="p_border"></div>

        <div class="s2_singleContent"></div>
        <div class="loader streamLoaderSingle"></div>
        <div class="test"></div>
    </div>
</div>


<script>

    $(document).ready(function() {

        s = new Stream("#wallStream", "<?php echo $startUrl; ?>", "<?php echo $reloadUrl; ?>", "<?php echo $singleEntryUrl; ?>");
<?php if ($readonly): ?>s.markAsReadOnly();<?php endif; ?>


<?php
// if we should show a single wall entry (e.g. search)
$wallEntryId = (int) Yii::app()->request->getParam('wallEntryId');
?>
<?php if ($wallEntryId) : ?>
            s.showItem(<?php echo $wallEntryId; ?>);
<?php else: ?>
            s.showStream();
<?php endif; ?>


        // Indicates this is the primary stream (not a module stream)
        mainStream = s;

        // Current active stream
        currentStream = s;

    });
</script>


