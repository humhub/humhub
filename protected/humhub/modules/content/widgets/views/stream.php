<?php

use \yii\web\View;

\humhub\modules\content\assets\Stream::register($this);

$this->registerJs('var streamUrl="' . $streamUrl . '"', View::POS_BEGIN);

$jsLoadWall = "s = new Stream('#wallStream');\n";
$wallEntryId = (int) Yii::$app->request->getQueryParam('wallEntryId');
if ($wallEntryId != "") {
    $jsLoadWall .= "s.showItem(" . $wallEntryId . ");\n";
} else {
    $jsLoadWall .= "s.showStream();\n";
}
$jsLoadWall .= "currentStream = s;\n";
$jsLoadWall .= "mainStream = s;\n";
$jsLoadWall .= "$('#btn-load-more').click(function() { currentStream.loadMore(); })\n";
$this->registerJs($jsLoadWall, View::POS_READY);
?>

<?php if ($this->context->showFilters) { ?>
    <ul class="nav nav-tabs wallFilterPanel" id="filter" style="display: none;">
        <li class=" dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo Yii::t('app', 'Filter'); ?> <b
                    class="caret"></b></a>
            <ul class="dropdown-menu">
                <li><a href="#" class="wallFilter" id="filter_entry_userinvoled"><i
                            class="fa fa-square-o"></i> <?php echo Yii::t('app', 'Where I´m involved'); ?></a>
                </li>
                <li><a href="#" class="wallFilter" id="filter_entry_mine"><i
                            class="fa fa-square-o"></i> <?php echo Yii::t('app', 'Created by me'); ?></a></li>

                <!-- post module related -->
                <li><a href="#" class="wallFilter" id="filter_entry_files"><i
                            class="fa fa-square-o"></i> <?php echo Yii::t('app', 'Content with attached files'); ?>
                    </a></li>
                <li><a href="#" class="wallFilter" id="filter_posts_links"><i
                            class="fa fa-square-o"></i> <?php echo Yii::t('app', 'Posts with links'); ?></a>
                </li>
                <li><a href="#" class="wallFilter" id="filter_model_posts"><i
                            class="fa fa-square-o"></i> <?php echo Yii::t('app', 'Posts only'); ?></a></li>
                <!-- /post module related -->

                <li class="divider"></li>

                <li><a href="#" class="wallFilter" id="filter_entry_archived"><i
                            class="fa fa-square-o"></i> <?php echo Yii::t('app', 'Include archived posts'); ?>
                    </a></li>
                <li><a href="#" class="wallFilter" id="filter_visibility_public"><i
                            class="fa fa-square-o"></i> <?php echo Yii::t('app', 'Only public posts'); ?></a>
                </li>
                <li><a href="#" class="wallFilter" id="filter_visibility_private"><i
                            class="fa fa-square-o"></i> <?php echo Yii::t('app', 'Only private posts'); ?></a>
                </li>
            </ul>
        </li>
        <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo Yii::t('app', 'Sorting'); ?>
                <b class="caret"></b></a>
            <ul class="dropdown-menu">
                <li><a href="#" class="wallSorting" id="sorting_c"><i
                            class="fa fa-check-square-o"></i> <?php echo Yii::t('app', 'Creation time'); ?></a></li>
                <li><a href="#" class="wallSorting" id="sorting_u"><i
                            class="fa fa-square-o"></i> <?php echo Yii::t('app', 'Last update'); ?></a></li>
            </ul>
        </li>
    </ul>
<?php } ?>

<div id="wallStream">

    <!-- DIV for a normal wall stream -->
    <div class="s2_stream" style="display:none">

        <div class="s2_streamContent"></div>
        <div class="loader streamLoader">
            <div class="sk-spinner sk-spinner-three-bounce">
                <div class="sk-bounce1"></div>
                <div class="sk-bounce2"></div>
                <div class="sk-bounce3"></div>
            </div>
        </div>

        <div class="emptyStreamMessage">

            <div class="placeholder <?php echo $this->context->messageStreamEmptyCss; ?>">
                <div class="panel">
                    <div class="panel-body">
                        <?php echo $this->context->messageStreamEmpty; ?>
                    </div>
                </div>
            </div>

        </div>
        <div class="emptyFilterStreamMessage">
            <div class="placeholder <?php echo $this->context->messageStreamEmptyWithFiltersCss; ?>">
                <div class="panel">
                    <div class="panel-body">
                        <?php echo $this->context->messageStreamEmptyWithFilters; ?>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- DIV for an single wall entry -->
    <div class="s2_single" style="display: none;">
        <div class="back_button_holder">
            <a href="#"
               class="singleBackLink btn btn-primary"><?php echo Yii::t('app', 'Back to stream'); ?></a><br><br>
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
