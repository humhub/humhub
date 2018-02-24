<?php

use humhub\modules\stream\assets\StreamAsset;
use humhub\widgets\Button;
use yii\helpers\Url;

/* @var $this \humhub\components\View */
/* @var $contentId integer */
/* @var $filters [] */
/* @var $streamUrl string */

StreamAsset::register($this);

$contentIdData = ($contentId != "") ? 'data-stream-contentid="' . $contentId . '"' : '';

?>

<?php if ($contentContainer && $contentContainer->isArchived()) : ?>
    <span class="label label-warning pull-right" style="margin-top:10px;"><?= Yii::t('ContentModule.widgets_views_label', 'Archived'); ?></span>
<?php endif; ?>

<!-- Stream filter section -->
<?php if ($this->context->showFilters) : ?>
    <?= $this->render('streamFilters', [
        'filters' => $filters,
        'contentContainer' => $contentContainer
    ]); ?>
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
            <div class="<?= $this->context->messageStreamEmptyCss; ?>">
                <div class="panel">
                    <div class="panel-body">
                        <?= $this->context->messageStreamEmpty; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="emptyFilterStreamMessage" style="display:none;">
            <div class="placeholder <?= $this->context->messageStreamEmptyWithFiltersCss; ?>">
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
    <?= Button::primary(Yii::t('ContentModule.widgets_views_stream', 'Load more'))
        ->id('btn-load-more')->action('loadMore', null, '#wallStream')->lg()?>
    <br/><br/>
</div>