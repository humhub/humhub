<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\stream\widgets;

use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use humhub\components\Widget;
use humhub\modules\content\components\ContentContainerActiveRecord;

/**
 * Stream View Widget creates a stream.
 *
 * @since 1.2
 */
class StreamViewer extends Widget
{

    /**
     * @var ContentContainerActiveRecord the content container if this stream belongs to one (optional)
     */
    public $contentContainer;

    /**
     * @var string the path to Stream Action to use
     */
    public $streamAction = "";

    /**
     * @since 1.1
     * @var array additional Params to add to Stream Action URL
     */
    public $streamActionParams = [];

    /**
     * Show default wall filters
     *
     * @var boolean
     */
    public $showFilters = true;

    /**
     * @var array filters to show
     */
    public $filters = [];

    /**
     * @var string the message when stream is empty and filters are active
     */
    public $messageStreamEmptyWithFilters = "";

    /**
     * 
     * @var string the CSS Class(es) for empty stream error with enabled filters
     */
    public $messageStreamEmptyWithFiltersCss = "";

    /**
     * @var string the message when stream is empty
     */
    public $messageStreamEmpty = "";

    /**
     * @var string the CSS Class(es) for message when stream is empty
     */
    public $messageStreamEmptyCss = "";

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->streamAction == "") {
            throw new Exception('You need to set the streamAction attribute to use this widget!');
        }

        // Add default Filters
        if (count($this->filters) === 0) {
            $this->filters['filter_entry_userinvolved'] = Yii::t('ContentModule.widgets_views_stream', 'Where I´m involved');
            $this->filters['filter_entry_mine'] = Yii::t('ContentModule.widgets_views_stream', 'Created by me');
            $this->filters['filter_entry_files'] = Yii::t('ContentModule.widgets_views_stream', 'Content with attached files');
            $this->filters['filter_posts_links'] = Yii::t('ContentModule.widgets_views_stream', 'Posts with links');
            $this->filters['filter_model_posts'] = Yii::t('ContentModule.widgets_views_stream', 'Posts only');
            $this->filters['filter_entry_archived'] = Yii::t('ContentModule.widgets_views_stream', 'Include archived posts');
            $this->filters['filter_visibility_public'] = Yii::t('ContentModule.widgets_views_stream', 'Only public posts');
            $this->filters['filter_visibility_private'] = Yii::t('ContentModule.widgets_views_stream', 'Only private posts');
        }

        // Setup default messages
        if ($this->messageStreamEmpty == "") {
            $this->messageStreamEmpty = Yii::t('ContentModule.widgets_views_stream', 'Nothing here yet!');
        }
        if ($this->messageStreamEmptyWithFilters == "") {
            $this->messageStreamEmptyWithFilters = Yii::t('ContentModule.widgets_views_stream', 'No matches with your selected filters!');
        }
    }

    /**
     * Creates url to stream BaseStreamAction including placeholders
     * which are replaced and handled by javascript.
     *
     * If a contentContainer is specified it will be used to create the url.
     *
     * @return string
     */
    protected function getStreamUrl()
    {
        $params = array_merge([
            'mode' => \humhub\modules\stream\actions\Stream::MODE_NORMAL
                ], $this->streamActionParams);

        if ($this->contentContainer) {
            return $this->contentContainer->createUrl($this->streamAction, $params);
        } else {
            array_unshift($params, $this->streamAction);
            return Url::to($params);
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $defaultStreamSort = Yii::$app->getModule('stream')->settings->get('defaultSort', 'c');

        $contentId = (int) Yii::$app->request->getQueryParam('contentId');

        return $this->render('stream', [
                    'streamUrl' => $this->getStreamUrl(),
                    'showFilters' => $this->showFilters,
                    'filters' => $this->filters,
                    'contentContainer' => $this->contentContainer,
                    'defaultStreamSort' => $defaultStreamSort,
                    'contentId' => $contentId,
        ]);
    }

}
