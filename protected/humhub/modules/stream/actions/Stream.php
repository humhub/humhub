<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\stream\actions;

use Yii;
use yii\base\Action;
use yii\base\Exception;
use yii\base\ActionEvent;

use humhub\modules\content\widgets\stream\StreamEntryWidget;
use humhub\modules\content\widgets\stream\StreamEntryOptions;
use humhub\modules\stream\models\StreamQuery;
use humhub\modules\stream\models\WallStreamQuery;
use humhub\modules\content\models\Content;
use humhub\modules\user\models\User;

/**
 * Stream is the basic action for content streams.
 *
 * JSON output structure:
 *      content             - array, content id is key
 *           id             - int, id of content
 *           guid           - string, guid of content
 *           pinned         - boolean, is content pinned
 *           archived       - boolean, i scontent is archived
 *           output         - string, the rendered html output of content
 *      total               - int, total of content records
 *      isLast              - boolean, is last content
 *      contentOrder        - array, list of content ids
 *
 *
 * @author luke
 * @since 0.11
 */
abstract class Stream extends Action
{
    use LegacyStreamTrait;

    /**
     * @event ActionEvent Event triggered before this action is run.
     * This can be used for example to customize [[activeQuery]] before it gets executed.
     * @since 1.1.1
     */
    const EVENT_BEFORE_RUN = 'beforeRun';

    /**
     * @event ActionEvent Event triggered after this action is run.
     * @since 1.1.1
     */
    const EVENT_AFTER_RUN = 'afterRun';

    /**
     * Sort by creation sort value
     */
    const SORT_CREATED_AT = 'c';

    /**
     * Sort by update sort value
     */
    const SORT_UPDATED_AT = 'u';

    /**
     * @var string
     * @deprecated since 1.6 use ActivityStreamAction
     */
    const MODE_NORMAL = 'normal';

    /**
     * @var string
     * @deprecated since 1.6 use ActivityStreamAction
     */
    const MODE_ACTIVITY = 'activity';

    /**
     * @var string
     * @deprecated since 1.7 use BaseStreamEntryWidget::VIEW_MODE_DASHBOARD
     */
    const FROM_DASHBOARD = 'dashboard';

    /**
     * Maximum wall entries per request
     */
    const MAX_LIMIT = 50;

    /**
     * Used to load single content entries.
     * @since 1.2
     */
    public $contentId;

    /**
     * First wall entry id to deliver
     *
     * @var int
     * @deprecated since 1.7 use $streamQuery->from
     */
    public $from;

    /**
     * Entry id of the top stream entry used for update requests
     *
     * @var int
     * @deprecated since 1.7 use $streamQuery->to
     */
    public $to;

    /**
     * Sorting Mode
     *
     * @var int
     */
    public $sort;

    /**
     * Maximum wall entries to return
     * @var int
     */
    public $limit = 4;

    /**
     * Filters
     *
     * @var array
     */
    public $filters = [];

    /**
     * Optional stream user
     * if no user is specified, the current logged in user will be used.
     *
     * @var User
     */
    public $user;

    /**
     * Used to filter the stream content entry classes against a given array.
     * @var array
     * @since 1.2
     */
    public $includes = [];

    /**
     * Used to filter our specific types
     * @var array
     * @since 1.2
     */
    public $excludes = [];

    /**
     * Stream query model instance
     * @var StreamQuery
     * @since 1.2
     */
    protected $streamQuery;

    /**
     * @var string suppress similar content types in a row
     */
    public $streamQueryClass = WallStreamQuery::class;

    /**
     * @var string can be used in special streams to force a specific stream entry widget to be used when rendering
     */
    public $streamEntryWidgetClass;

    /**
     * @var StreamEntryOptions default render option for stream entries initialized by [[initStreamEntryOptions()]]
     */
    public $streamEntryOptions;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->excludes = array_merge($this->excludes, Yii::$app->getModule('stream')->streamExcludes);

        $this->streamQuery = $this->initQuery();

        if (!Yii::$app->request->isConsoleRequest) {
            $this->streamQuery->load(Yii::$app->request->get());
        }

        $this->beforeApplyFilters();

        $this->streamQuery->query(true);

        $this->afterApplyFilters();
    }

    /**
     * Initializes the StreamQuery instance. By default [[streamQueryClass]] property will be used to initialize the instance.
     *
     * Subclasses may overwrite this function in order to add or remove custom stream filters or set other default
     * settings of your StreamQuery instance.
     *
     * Example usage:
     *
     * ```php
     * protected function initQuery($options = [])
     * {
     *   $query = parent::initQuery($options);
     *   $query->addFilterHandler(new CustomStreamFilter());
     *   return $query;
     * }
     * ```
     *
     * @param array $options instance attribute options
     * @return StreamQuery
     * @since 1.6
     */
    protected function initQuery($options = [])
    {
        $streamQueryClass = $this->streamQueryClass;
        /* @var $instance StreamQuery */
        $instance = $streamQueryClass::find();
        $instance->setAttributes($options, false);
        return $instance;
    }

    /**
     * This function is called right before the StreamQuery is built and all filters are applied.
     * At this point the StreamQuery has already been loaded with request data.
     * Subclasses may overwrite this function in order to do some last settings on the StreamQuery instance.
     *
     * When overriding this method, make sure you call the parent implementation at the beginning of your function.
     */
    protected function beforeApplyFilters()
    {
        // Merge configured filters set for this action with request filters.
        $this->streamQuery->addFilter($this->filters);

        $this->streamQuery->includes($this->includes);
        $this->streamQuery->excludes($this->excludes);
        $this->streamQuery->forUser($this->user);

        // Overwrite limit if there was no setting in the request.
        if (empty($this->streamQuery->limit)) {
            $this->streamQuery->limit = $this->limit;
        }

        // Overwrite sort if there was no setting in the request.
        if (empty($this->streamQuery->sort)) {
            $this->streamQuery->sort = $this->sort;
        }
    }

    /**
     * This function is called after the StreamQuery was build and all filters are applied. At this point changing
     * most StreamQuery settings as filters won't have any effect. Since the query is not yet executed the
     * StreamQuery->query() can still be used for custom query conditions.
     *
     * When overriding this method, make sure you call the parent implementation at the beginning of your function.
     */
    protected function afterApplyFilters()
    {
        $this->setDeprecatedActionProperties();

        // Update action filters with merged request and configured action filters.
        $this->filters = $this->streamQuery->filters;
        $this->user = $this->streamQuery->user;

        if(!$this->streamEntryOptions) {
            $this->streamEntryOptions = $this->initStreamEntryOptions();
        }

        if($this->streamEntryWidgetClass) {
            $this->streamEntryOptions->overwriteWidgetClass($this->streamEntryWidgetClass);
        }
    }

    /**
     * @return StreamEntryOptions
     */
    protected function initStreamEntryOptions()
    {
        $instance = new StreamEntryOptions();
        if($this->streamQuery->isSingleContentQuery()) {
            $instance->viewContext(StreamEntryOptions::VIEW_CONTEXT_DETAIL);
        }
        return $instance;
    }

    /**
     * This method is called right before `run()` is executed.
     * You may override this method to do preparation work for the action run.
     * If the method returns false, it will cancel the action.
     *
     * @return boolean whether to run the action.
     */
    protected function beforeRun()
    {
        $event = new ActionEvent($this);
        $this->trigger(self::EVENT_BEFORE_RUN, $event);
        return $event->isValid;
    }

    /**
     * @inheritdoc
     * @throws \Throwable
     */
    public function run()
    {
        $response = new StreamResponse($this->streamQuery);

        foreach ($this->streamQuery->all() as $content) {
            $streamEntry = $this->getStreamEntryResult($content, $this->streamEntryOptions);
            if($streamEntry) {
                $response->addEntry($content->id, $streamEntry);
            }
        }

        return $response->asJson();
    }

    /**
     * This method is called right after `run()` is executed.
     * You may override this method to do post-processing work for the action run.
     */
    protected function afterRun()
    {
        $this->beforeApplyFilters();
        $event = new ActionEvent($this);
        $this->trigger(self::EVENT_AFTER_RUN, $event);
    }

    /**
     * @param Content $content
     * @param StreamEntryOptions|null $options
     * @return array|null
     * @throws \Throwable
     */
    private function getStreamEntryResult(Content $content, StreamEntryOptions $options = null)
    {
        try {
            if (!$content->getModel()) {
                throw new Exception('Could not get contents underlying object! - contentid: ' . $content->id);
            }

            if (!is_subclass_of($content->getModel()->wallEntryClass, StreamEntryWidget::class, true)) {
                return static::getContentResultEntry($content);
            }

            return StreamEntryResponse::getAsArray($content, $options);
        } catch (\Throwable $e) {
            // Don't kill the stream action in prod environments in case the rendering of an entry fails.
            if (YII_ENV_PROD) {
                Yii::error($e);
            } else {
                throw $e;
            }
        }

        return null;
    }
}
