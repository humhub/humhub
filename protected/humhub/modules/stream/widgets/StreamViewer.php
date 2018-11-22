<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\stream\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use humhub\modules\topic\models\Topic;
use humhub\widgets\JsWidget;
use humhub\modules\content\components\ContentContainerActiveRecord;

/**
 * Stream View Widget creates a stream.
 *
 * @since 1.2
 */
class StreamViewer extends JsWidget
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
     * @var string definition of stream filter navigation widget class
     */
    public $streamFilterNavigation = WallStreamFilterNavigation::class;

    /**
     * @var array list of active filters filters to show this will be set as [[StreamFilter::definition]] when rendering the filter navigation
     */
    public $filters = [];

    /**
     * Show default wall filters
     *
     * @var boolean
     */
    public $showFilters = true;

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
    public $jsWidget = 'stream.wall.WallStream';

    /**
     * @var string stream view
     * @since 1.3
     */
    public $view = '@stream/widgets/views/wallStream';

    /**
     * @inheritdoc
     */
    public $id = 'wallStream';

    /**
     * @inheritdoc
     */
    public $init = true;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        // Setup default messages
        if ($this->messageStreamEmpty == "") {
            $this->messageStreamEmpty = Yii::t('ContentModule.widgets_views_stream', 'Nothing here yet!');
        }
        if ($this->messageStreamEmptyWithFilters == "") {
            $this->messageStreamEmptyWithFilters = Yii::t('ContentModule.widgets_views_stream', 'No matches with your selected filters!');
        }
    }

    public function getData()
    {
        $result = [
            'content-delete-url' => Url::to(['/content/content/delete']),
            'stream' => $this->getStreamUrl(),
            'stream-empty-message' => $this->messageStreamEmpty,
            'stream-empty-class' => $this->messageStreamEmptyCss,
            'stream-empty-filter-message' => $this->messageStreamEmptyWithFilters,
            'stream-empty-filter-class' => $this->messageStreamEmptyWithFiltersCss
        ];

        if (!empty(Yii::$app->request->getQueryParam('contentId'))) {
            $result['stream-contentid'] = Yii::$app->request->getQueryParam('contentId');
        }

        if (Yii::$app->request->getQueryParam('topicId')) {
            $topic = Topic::findOne((int) Yii::$app->request->getQueryParam('topicId'));
            if ($topic) {
                $result['stream-topic'] = ['id' => $topic->id, 'name' => $topic->name];
            }
        }

        return $result;
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
        if ($this->contentContainer) {
            return $this->contentContainer->createUrl($this->streamAction, $this->streamActionParams);
        } else {
            $params = $this->streamActionParams;
            array_unshift($params, $this->streamAction);
            return Url::to($params);
        }
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function run()
    {
        if (empty($this->streamAction)) {
            throw new InvalidConfigException('You need to set the streamAction attribute to use this widget!');
        }

        $filterNav = ($this->showFilters && !empty($this->streamFilterNavigation)) ? call_user_func($this->streamFilterNavigation.'::widget', [
            'definition' => $this->filters
        ]) : '';

        return $this->render($this->view, [
                'filterNav' => $filterNav,
                'contentContainer' => $this->contentContainer,
                'options' => $this->getOptions(),
        ]);
    }
}
