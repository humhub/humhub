<?php
use yii\helpers\Url;

\humhub\modules\stream\assets\StreamAsset::register($this);

$contentId = (int) Yii::$app->request->getQueryParam('wallEntryId');
$contentIdData = ($contentId != "") ? 'data-stream-contentid="'.$contentId.'"' : '' ;    

$defaultStreamSort = Yii::$app->getModule('content')->settings->get('stream.defaultSort');
$this->registerJsVar('defaultStreamSort', ($defaultStreamSort != '') ? $defaultStreamSort : 'c');
?>

<!-- Stream filter section -->
<?php if ($this->context->showFilters) { ?>
    <ul class="nav nav-tabs wallFilterPanel" id="filter" style="display: none;">
        <li class=" dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo Yii::t('ContentModule.widgets_views_stream', 'Filter'); ?> <b
                    class="caret"></b></a>
            <ul class="dropdown-menu">
                <?php foreach ($filters as $filterId => $filterTitle): ?>
                    <li>
                        <a href="#" class="wallFilter" id="<?php echo $filterId; ?>">
                            <i class="fa fa-square-o"></i> <?php echo $filterTitle; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </li>
        <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo Yii::t('ContentModule.widgets_views_stream', 'Sorting'); ?>
                <b class="caret"></b></a>
            <ul class="dropdown-menu">
                <li><a href="#" class="wallSorting" id="sorting_c"><i
                            class="fa fa-square-o"></i> <?php echo Yii::t('ContentModule.widgets_views_stream', 'Creation time'); ?></a></li>
                <li><a href="#" class="wallSorting" id="sorting_u"><i
                            class="fa fa-square-o"></i> <?php echo Yii::t('ContentModule.widgets_views_stream', 'Last update'); ?></a></li>
            </ul>
        </li>
    </ul>
<?php } ?>

<!-- Stream content -->
<div id="wallStream" data-stream="<?= $streamUrl ?>" <?= $contentIdData ?> 
     data-action-component="humhub.modules.stream.Stream" 
     data-content-delete-url="<?= Url::to(['/content/content/delete']) ?>">
    
    <!-- DIV for a normal wall stream -->
    <div class="s2_stream" style="display:none">
        <div class="back_button_holder" style="display:none">
            <a href="#" class="singleBackLink btn btn-primary"><?php echo Yii::t('ContentModule.widgets_views_stream', 'Back to stream'); ?></a><br><br>
        </div>
        <div class="s2_streamContent"></div>
        <?php echo \humhub\widgets\LoaderWidget::widget(['cssClass' => 'streamLoader']); ?>

        <div class="emptyStreamMessage" style="display:none;">
            <div class="<?php echo $this->context->messageStreamEmptyCss; ?>">
                <div class="panel">
                    <div class="panel-body">
                        <?php echo $this->context->messageStreamEmpty; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="emptyFilterStreamMessage" style="display:none;">
            <div class="placeholder <?php echo $this->context->messageStreamEmptyWithFiltersCss; ?>">
                <div class="panel">
                    <div class="panel-body">
                        <?php echo $this->context->messageStreamEmptyWithFilters; ?>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- show "Load More" button on mobile devices -->
<div class="col-md-12 text-center visible-xs visible-sm">
    <button id="btn-load-more" class="btn btn-primary btn-lg ">Load more</button>
    <br/><br/>
</div>