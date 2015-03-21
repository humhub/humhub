<?php
/**
 * This view shows the streaming wall by WallStreamWidget.
 *
 * @property String $reloadUrl is the url to load more entries
 * @property String $startUrl is the url to load the first entries
 * @property String $singleEntryUrl is the url to load a single entry
 *
 * @package humhub.modules_core.wall
 * @since 0.5
 */
?>
<?php if (Yii::app()->getController()->id != 'dashboard') {  ?>
<ul class="nav nav-tabs wallFilterPanel" id="filter" style="display: none;">
    <li class=" dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo Yii::t('WallModule.widgets_views_stream', 'Filter'); ?> <b
                class="caret"></b></a>
        <ul class="dropdown-menu">
            <li><a href="#" class="wallFilter" id="filter_entry_userinvoled"><i
                        class="fa fa-square-o"></i> <?php echo Yii::t('WallModule.widgets_views_stream', 'Where I´m involved'); ?></a>
            </li>
            <li><a href="#" class="wallFilter" id="filter_entry_mine"><i
                        class="fa fa-square-o"></i> <?php echo Yii::t('WallModule.widgets_views_stream', 'Created by me'); ?></a></li>

            <!-- post module related -->
            <li><a href="#" class="wallFilter" id="filter_entry_files"><i
                        class="fa fa-square-o"></i> <?php echo Yii::t('WallModule.widgets_views_stream', 'Content with attached files'); ?>
                </a></li>
            <li><a href="#" class="wallFilter" id="filter_posts_links"><i
                        class="fa fa-square-o"></i> <?php echo Yii::t('WallModule.widgets_views_stream', 'Posts with links'); ?></a>
            </li>
            <li><a href="#" class="wallFilter" id="filter_model_posts"><i
                        class="fa fa-square-o"></i> <?php echo Yii::t('WallModule.widgets_views_stream', 'Posts only'); ?></a></li>
            <!-- /post module related -->

            <li class="divider"></li>

            <li><a href="#" class="wallFilter" id="filter_entry_archived"><i
                        class="fa fa-square-o"></i> <?php echo Yii::t('WallModule.widgets_views_stream', 'Include archived posts'); ?>
                </a></li>
            <li><a href="#" class="wallFilter" id="filter_visibility_public"><i
                        class="fa fa-square-o"></i> <?php echo Yii::t('WallModule.widgets_views_stream', 'Only public posts'); ?></a>
            </li>
            <li><a href="#" class="wallFilter" id="filter_visibility_private"><i
                        class="fa fa-square-o"></i> <?php echo Yii::t('WallModule.widgets_views_stream', 'Only private posts'); ?></a>
            </li>
        </ul>
    </li>
    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo Yii::t('WallModule.widgets_views_stream', 'Sorting'); ?>
            <b class="caret"></b></a>
        <ul class="dropdown-menu">
            <li><a href="#" class="wallSorting" id="sorting_c"><i
                        class="fa fa-check-square-o"></i> <?php echo Yii::t('WallModule.widgets_views_stream', 'Creation time'); ?></a></li>
            <li><a href="#" class="wallSorting" id="sorting_u"><i
                        class="fa fa-square-o"></i> <?php echo Yii::t('WallModule.widgets_views_stream', 'Last update'); ?></a></li>
        </ul>
    </li>
</ul>
<?php } ?>

<div id="wallStream">

    <!-- DIV for an normal wall stream -->
    <div class="s2_stream" style="display:none">

        <div class="s2_streamContent"></div>
        <div class="loader streamLoader"></div>

        <div class="emptyStreamMessage">
            
            
            <?php if ($type == Wall::TYPE_COMMUNITY): ?>
                <div class="placeholder placeholder-empty-stream">
                    <?php echo Yii::t('WallModule.widgets_views_stream', '<b>Nobody wrote something yet.</b><br>Make the beginning and post something...'); ?>
                </div>
            <?php endif; ?>
            <?php if ($type == Wall::TYPE_DASHBOARD): ?>
                <div class="placeholder">
                    <?php echo Yii::t('WallModule.widgets_views_stream', '<b>Your dashboard is empty!</b><br>Post something on your profile or join some spaces!'); ?>
                </div>

            <?php elseif ($type == Wall::TYPE_USER): ?>

                <?php if ($this->contentContainer->id == Yii::app()->user->id) { ?>
                    <div class="placeholder placeholder-empty-stream">
                        <?php echo Yii::t('WallModule.widgets_views_stream', '<b>Your profile stream is still empty</b><br>Get started and post something...'); ?>
                    </div>
                <?php } else { ?>
                    <div class="panel panel-default">
                        <div class="panel-body">
                        <?php echo Yii::t('WallModule.widgets_views_stream', '<b>This profile stream is still empty</b>'); ?>
                        </div>
                    </div>
                <?php } ?>
            <?php
            elseif ($type == Wall::TYPE_SPACE): ?>
                <?php if ($this->contentContainer->canWrite()) { ?>
                <div class="placeholder placeholder-empty-stream">
                    <?php echo Yii::t('WallModule.widgets_views_stream', '<b>This space is still empty!</b><br>Start by posting something here...'); ?>
                </div>
            <?php }?>
            <?php endif; ?>
        </div>

        <div class="emptyFilterStreamMessage">
            <div class="placeholder">
                <b><?php echo Yii::t('WallModule.widgets_views_stream', 'Nothing found which matches your current filter(s)!'); ?></b>
            </div>
        </div>

    </div>

    <!-- DIV for an single wall entry -->
    <div class="s2_single" style="display: none;">
        <div class="back_button_holder">
            <a href="#"
               class="singleBackLink btn btn-primary"><?php echo Yii::t('WallModule.widgets_views_stream', 'Back to stream'); ?></a><br><br>
        </div>
        <div class="p_border"></div>

        <div class="s2_singleContent"></div>
        <div class="loader streamLoaderSingle"></div>
        <div class="test"></div>
    </div>
</div>

<!-- show "Load More" button on mobile devices -->
<div class="col-md-12 text-center visible-xs visible-sm">
    <button id="btn-load-more" class="btn btn-primary btn-lg ">Load more</button>
    <br/><br/>
</div>


<script>

    $(document).ready(function () {

        s = new Stream("#wallStream", "<?php echo $startUrl; ?>", "<?php echo $reloadUrl; ?>", "<?php echo $singleEntryUrl; ?>");
        <?php if (false): ?>s.markAsReadOnly();
        <?php endif; ?>


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

    $('#btn-load-more').click(function() {
        // load next wall entries
        currentStream.loadMore();
    })

</script>


