<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\modules\content\models\ContentType;
use humhub\modules\post\models\Post;
use humhub\modules\stream\widgets\StreamFilterBlock;
use humhub\modules\topic\widgets\TopicPicker;
use humhub\widgets\Button;
use humhub\widgets\ModalButton;
use humhub\widgets\MultiSelectField;

/* @var $this \humhub\components\View */
/* @var $filters [] */
/* @var $contentContainer \humhub\modules\content\components\ContentContainerActiveRecord|null */

$sorting = Yii::$app->getModule('stream')->settings->get('defaultSort', 'c');

$basicFilter = ['filter_entry_userinvolved', 'filter_entry_mine', 'filter_entry_files'];
$visibilityFilter = ['filter_visibility_public', 'filter_visibility_private', 'filter_entry_archived'];
$postFilter = ['filter_model_posts', 'filter_posts_links'];
$sortFilter = ['sorting_c', 'sorting_u'];

$contentTypeSelection = ContentType::getContentTypeSelection($contentContainer);
unset($contentTypeSelection[Post::class])

?>

<div id="stream-filter-panel" class="wallFilterPanel">
    <div class="nav-tabs">
        <div id="stream-filter-panel-nav" class="clearfix">
            <div id="stream-filter-bar">
                <?= ModalButton::defaultType()->icon('fa-plus')->xs()->action('stream.focusTopicFilter')
                    ->tooltip(Yii::t('ContentModule.widgets_views_stream', 'Add Topic Filter'))->loader(false)->right(); ?>
            </div>
            <?= Button::asLink(Yii::t('ContentModule.widgets_views_stream', 'Filter') . '<b class="caret"></b>')
                ->id('stream-filter-toggle')->icon('fa-filter')->sm()->style('pa') ?>
        </div>

        <div class="filter-panel-body" style="display:none">
            <div class="filter-container">
                <div class="filter-col">
                    <?= StreamFilterBlock::widget(['block' => $basicFilter, 'filters' => $filters, 'title' => Yii::t('ContentModule.widgets_views_stream', 'Basic Filter')]); ?>
                    <?= StreamFilterBlock::widget(['block' => $postFilter, 'filters' => $filters, 'title' => Yii::t('ContentModule.widgets_views_stream', 'Post Filter')]); ?>
                </div>
                <div class="filter-col">
                    <?= StreamFilterBlock::widget(['block' => $visibilityFilter, 'filters' => $filters, 'title' => Yii::t('ContentModule.widgets_views_stream', 'Visibility')]); ?>
                    <?= StreamFilterBlock::widget(['block' => $sortFilter, 'filters' => $filters, 'title' => Yii::t('ContentModule.widgets_views_stream', 'Sorting')]); ?>
                </div>
                <div class="filter-col">
                    <strong><?= Yii::t('ContentModule.widgets_views_stream', 'Content Type') ?></strong>
                    <?= MultiSelectField::widget([
                        'id' => 'stream_filter_content_type',
                        'name' => 'filter_content_type',
                        'items' => $contentTypeSelection
                    ]); ?>

                    <strong><?= Yii::t('ContentModule.widgets_views_stream', 'Topics') ?></strong>
                    <?= TopicPicker::widget([
                        'id' => 'stream_filter_topic',
                        'name' => 'filter_topic'
                    ]); ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    var toggleFilterPanel = function () {
        debugger;
        $('#stream-filter-panel').find('.filter-panel-body').slideToggle();
    };

    $('#stream-filter-toggle').on('click', function (evt) {
        evt.preventDefault();
        evt.stopImmediatePropagation();
        toggleFilterPanel();
    });

    $('#stream-filter-panel-nav').on('click', function (evt) {
        if (!$(evt.target).closest('a').length) {
            evt.preventDefault();
            toggleFilterPanel();
        }
    })
</script>