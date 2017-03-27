<?php

use yii\helpers\Url;

\humhub\modules\stream\assets\StreamAsset::register($this);

$sorting = Yii::$app->getModule('stream')->settings->get('defaultSort', 'c');

$this->registerJsConfig([
    'stream' => [
        'horizontalImageScrollOnMobile' => Yii::$app->settings->get('horImageScrollOnMobile'),
        'defaultSort' => $sorting,
        'text' => [
            'success.archive' => Yii::t('ContentModule.widgets_views_stream', 'The content has been archived.'),
            'success.unarchive' => Yii::t('ContentModule.widgets_views_stream', 'The content has been unarchived.'),
            'success.pin' => Yii::t('ContentModule.widgets_views_stream', 'The content has been pinned.'),
            'success.unpin' => Yii::t('ContentModule.widgets_views_stream', 'The content has been unpinned.'),
            'success.delete' => Yii::t('ContentModule.widgets_views_stream', 'The content has been deleted.'),
            'info.editCancel' => Yii::t('ContentModule.widgets_views_stream', 'Your last edit state has been saved!'),
        ]
    ]
]);

$contentIdData = ($contentId != "") ? 'data-stream-contentid="' . $contentId . '"' : '';
?>

<!-- Stream filter section -->
<?php if ($contentContainer && $contentContainer->isArchived()) : ?>
    <span class="label label-warning pull-right" style="margin-top:10px;"><?= Yii::t('ContentModule.widgets_views_label', 'Archived'); ?></span>
<?php endif; ?>
<?php if ($this->context->showFilters) : ?>
    <ul class="nav nav-tabs wallFilterPanel" id="filter" style="display: none;">
        <li class=" dropdown">
            <a class="stream-filter dropdown-toggle" data-toggle="dropdown" href="#"><?= Yii::t('ContentModule.widgets_views_stream', 'Filter'); ?> <b
                    class="caret"></b></a>
            <ul class="dropdown-menu">
                <?php foreach ($filters as $filterId => $filterTitle): ?>
                    <li>
                        <a href="#" class="wallFilter" id="<?= $filterId; ?>">
                            <i class="fa fa-square-o"></i> <?= $filterTitle; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </li>
        <li class="dropdown">
            <a class="stream-sorting dropdown-toggle" data-toggle="dropdown" href="#"><?= Yii::t('ContentModule.widgets_views_stream', 'Sorting'); ?>
                <b class="caret"></b></a>
            <ul class="dropdown-menu">
                <li>
                    <a href="#" class="wallSorting" id="sorting_c">
                        <i class="fa <?= ($sorting === 'c') ? 'fa-check-square-o' : 'fa-square-o'?>"></i> <?= Yii::t('ContentModule.widgets_views_stream', 'Creation time'); ?>
                    </a>
                </li>
                <li>
                    <a href="#" class="wallSorting" id="sorting_u">
                        <i class="fa <?= ($sorting === 'u') ? 'fa-check-square-o' : 'fa-square-o'?>"></i> <?= Yii::t('ContentModule.widgets_views_stream', 'Last update'); ?>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
<?php endif; ?>

<!-- Stream content -->
<div id="wallStream" data-stream="<?= $streamUrl ?>" <?= $contentIdData ?> 
     data-action-component="stream.WallStream" 
     data-content-delete-url="<?= Url::to(['/content/content/delete']) ?>">

    <!-- DIV for a normal wall stream -->
    <div class="s2_stream">
        <div class="back_button_holder" style="display:none">
            <a href="#" class="singleBackLink btn btn-primary"><?= Yii::t('ContentModule.widgets_views_stream', 'Back to stream'); ?></a><br><br>
        </div>
        <div class="s2_streamContent" data-stream-content></div>

        <div class="emptyStreamMessage" style="display:none;">
            <div class="<?php echo $this->context->messageStreamEmptyCss; ?>">
                <div class="panel">
                    <div class="panel-body">
                        <?= $this->context->messageStreamEmpty; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="emptyFilterStreamMessage" style="display:none;">
            <div class="placeholder <?php echo $this->context->messageStreamEmptyWithFiltersCss; ?>">
                <div class="panel">
                    <div class="panel-body">
                        <?= $this->context->messageStreamEmptyWithFilters; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- show "Load More" button on mobile devices -->
<div class="col-md-12 text-center visible-xs visible-sm">
    <button id="btn-load-more" data-action-click="loadMore" data-action-target="#wallStream" data-ui-loader class="btn btn-primary btn-lg "><?= Yii::t('ContentModule.widgets_views_stream', 'Load more'); ?></button>
    <br/><br/>
</div>