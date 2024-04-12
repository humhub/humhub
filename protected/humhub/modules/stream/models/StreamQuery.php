<?php

namespace humhub\modules\stream\models;

use humhub\modules\content\models\Content;
use humhub\modules\stream\actions\Stream;
use humhub\modules\stream\models\filters\BlockedUsersStreamFilter;
use humhub\modules\stream\models\filters\ContentTypeStreamFilter;
use humhub\modules\stream\models\filters\DateStreamFilter;
use humhub\modules\stream\models\filters\DefaultStreamFilter;
use humhub\modules\stream\models\filters\DraftContentStreamFilter;
use humhub\modules\stream\models\filters\OriginatorStreamFilter;
use humhub\modules\stream\models\filters\ScheduledContentStreamFilter;
use humhub\modules\stream\models\filters\StreamQueryFilter;
use humhub\modules\stream\models\filters\TopicStreamFilter;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * Description of StreamQuery
 *
 * @author buddha
 * @since 1.2
 */
class StreamQuery extends Model
{
    /**
     * @event Event triggered before filterHandlers are applied, this can be used to add custom stream filters.
     */
    public const EVENT_BEFORE_FILTER = 'beforeFilter';

    /**
     * @event Event triggered after filterHandlers are applied.
     */
    public const EVENT_AFTER_FILTER = 'afterFilter';

    /**
     * Default channels
     */
    public const CHANNEL_DEFAULT = 'default';
    public const CHANNEL_ACTIVITY = 'activity';

    /**
     * Maximum wall entries per request
     */
    public const MAX_LIMIT = 20;

    /**
     * Can be set to filter specific content types.
     *
     * @var array Content type filter
     */
    public $includes;

    /**
     * @var string stream channel to display
     */
    public $channel = self::CHANNEL_DEFAULT;

    /**
     * Can be set to filter out specific content types.
     *
     * @var array Content type filter
     */
    public $excludes;

    /**
     * The user which requested the stream. By default the current user identity.
     * @var User
     */
    public $user;

    /**
     * Can be set to filter content of a specific user
     * @var User
     */
    public $originator;

    /**
     * Can be set to request a single content instance.
     * @var int
     */
    public $contentId;

    /**
     * Start contentId used for stream "pagination".
     * @var int
     */
    public $from = 0;

    /**
     * Top contentId used for stream updates.
     * @var int
     * @since 1.5
     */
    public $to;

    /**
     * Stream sorting default = Stream::SORT_CREATED_AT;
     * @var string
     */
    public $sort;

    /**
     * Result count limit.
     * @var int
     */
    public $limit;

    /**
     * Array of stream filters to apply to the query.
     * There are the following filter available:
     *
     *  - 'entry_files': Filters content with attached files
     *  - 'entry_mine': Filters only content created by the query $user
     *  - 'entry_userinvovled': Filter content the query $user is involved
     *  - 'visibility_private': Filter only private content
     *  - 'visibility_public': Filter only public content
     *
     * > Note: Since v1.3 those filters are forwarded to a [[DefaultStreamFilter]].
     *
     * @var array
     */
    public $filters = [];

    /**
     * @var array additional query filter handler
     * @see [[setupFilters()]]
     * @since 1.3
     */
    public $filterHandlers = [
        DefaultStreamFilter::class,
        TopicStreamFilter::class,
        ContentTypeStreamFilter::class,
        OriginatorStreamFilter::class,
        BlockedUsersStreamFilter::class,
        DateStreamFilter::class,
        DraftContentStreamFilter::class,
        ScheduledContentStreamFilter::class,
    ];

    /**
     * @var array Excluded query filter handlers from adding
     * @see [[removeFilterHandler(..., $exclude = true)]]
     * @since 1.14.5
     */
    public $excludedFilterHandlers = [];

    /**
     * Per default only content with published state are returned.
     *
     * @note Used, for example, by the Recycle Bin module to display deleted content in the stream.
     * @var array
     */
    public $stateFilterCondition = ['OR', ['content.state' => Content::STATE_PUBLISHED]];

    /**
     * The content query.
     *
     * @var ActiveQuery
     */
    protected $_query;

    /**
     * @var bool query built
     */
    protected $_built = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['limit', 'from', 'to', 'contentId'], 'number'],
            [['sort'], 'safe'],
        ];
    }

    /**
     * Static initializer.
     *
     * @param array|string|int $includes either an array of ContentActiveRecord class names or single class name or single contentId.
     * @param array|string $excludes either an array of ContentActiveRecord class names or single class name to exclude from the query.
     * @return static
     */
    public static function find($includes = [], $excludes = [])
    {
        $instance = new static();

        if (!is_int($includes)) {
            //Allow single type
            if (!is_array($includes)) {
                $includes = [$includes];
            }

            if (!is_array($excludes)) {
                $excludes = [$excludes];
            }
        } else {
            $instance->contentId = $includes;
        }

        return $instance->includes($includes)->excludes($excludes);
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->_query = Content::find();
        // Set default user after initialization so it's avialable without assambling the query.
        $this->checkUser();
    }

    /**
     * Builder function for single content stream query.
     *
     * @param $contentId int
     * @return static
     */
    public function content($contentId)
    {
        if (!is_int($contentId)) {
            $this->contentId = $contentId;
        }
        return $this;
    }

    /**
     * Builder function used to set the user perspective of the stream.
     *
     * @param $user |null User if null the current user identity will be used
     * @return static
     * @see checkUser
     */
    public function forUser($user = null)
    {
        $this->user = $user;
        $this->checkUser();
        return $this;
    }

    /**
     * Builder function to overwrite the active filters.
     *
     * @param array|string $filters
     * @return static
     */
    public function filters($filters = [])
    {
        $this->filters = (is_string($filters)) ? [$filters] : $filters;
        return $this;
    }

    /**
     * Builder function to add a single or multiple filters to the current set of active filters.
     *
     * @param $filters
     * @return static
     */
    public function addFilter($filters)
    {
        if (is_string($filters)) {
            $this->filters[] = $filters;
        } elseif (is_array($filters)) {
            $this->filters = ArrayHelper::merge($this->filters, $filters);
        }
        return $this;
    }

    /**
     * Builder function used to set the stream channel filter.
     *
     * @param string $channel
     * @return static
     */
    public function channel($channel)
    {
        $this->channel = $channel;
        $this->_query->andWhere(['content.stream_channel' => $channel]);
        return $this;
    }


    /**
     * Builder function used to set the $includes array in order to only include specific content types.
     *
     * @param string[]|string $includes
     * @return static
     */
    public function includes($includes = [])
    {
        if (is_string($includes)) {
            $this->includes = [$includes];
        } elseif (is_array($includes)) {
            $this->includes = $includes;
        }

        return $this;
    }

    public function excludes($types = [])
    {
        if (is_string($types)) {
            $this->excludes = [$types];
        } elseif (is_array($types)) {
            $this->excludes = $types;
        }

        return $this;
    }

    /**
     * Builder function for query $sort field.
     *
     * @param $sort string stream sorting either [[Stream::SORT_CREATED_AT]] or [[Stream::SORT_UPDATED_AT]]
     * @return static
     * @since 1.5
     */
    public function sort($sort)
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * Builder function used to set the $originator filter.
     *
     * @param $user User
     * @return static
     */
    public function originator($user)
    {
        $this->originator = $user;
        return $this;
    }

    /**
     * Builder function used to set the $from filter. The result will only include older entries while respecting
     * the $order setting. This function can be used for stream pagination (load more).
     *
     * > Note: the content entry with the id $from will not be included itself!
     *
     * @param $from int content id used for pagination
     * @return static
     */
    public function from($from = 0)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * Builder function used to set the $to filter. The result will only include newer entries while respecting
     * the $order setting. This function can be used for stream updates.
     *
     * > Note: the content entry with the id $to will not be included itself!
     *
     * @param $from int content id used for pagination
     * @return static
     */
    public function to($to = null)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * Builder function used to set the result limit.
     *
     * @param int $limit
     * @return static
     */
    public function limit($limit = self::MAX_LIMIT)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Returns the underlying ActiveQuery object.
     *
     * @param bool $build weather or not the query should be build by means of the given filters
     * @return ActiveQuery
     */
    public function query($build = false)
    {

        if ($build && !$this->_built) {
            $this->setupQuery();
        }

        return $this->_query;
    }

    /**
     * Returns the query result.
     *
     * @return Content[]
     */
    public function all()
    {
        return $this->postProcessAll(
            $this->query(!$this->_built)->all(),
        );
    }

    /**
     * @param Content[] $result
     * @return Content[]
     * @since 1.14
     */
    protected function postProcessAll(array $result)
    {
        /**
         * Allow FilterHandler to directly modify the stream content result
         * e.g. (e.g. Pinned/Drafts) can inject additional content on the first stream batch
         */
        foreach ($this->filterHandlers as $filterHandler) {
            if ($filterHandler instanceof StreamQueryFilter) {
                $filterHandler->postProcessStreamResult($result);
            } else {
                Yii::warning('StreamQuery::postProcessAll - invalid FilterHandler: ' . var_export($filterHandler, true), 'content');
            }
        }

        // Remove duplicates, they may exist after fetch top sorted items by different way,
        // e.g. when a Content is top sorted as Draft and as Unread News
        return array_values(ArrayHelper::index($result, 'id'));
    }

    /**
     * Builds up the query based on the active filters.
     */
    protected function setupQuery()
    {
        $this->checkUser();
        $this->checkSort();
        $this->checkLimit();
        $this->checkFrom();
        $this->checkTo();
        $this->setupCriteria();
        $this->setupFilters();

        $this->_query->andWhere($this->stateFilterCondition);

        if (!empty($this->channel)) {
            $this->channel($this->channel);
        }

        $this->_built = true;
    }

    /**
     * Sets the user identity as default user perspective in case no user was set manually.
     */
    protected function checkUser()
    {
        if ($this->user === null && !Yii::$app->user->isGuest) {
            $this->user = Yii::$app->user->getIdentity();
        }
    }

    /**
     * Sets the default stream sort order in case no or an invalid sort order has been set manually.
     */
    protected function checkSort()
    {
        if (empty($this->sort) || !in_array($this->sort, [Stream::SORT_CREATED_AT, Stream::SORT_UPDATED_AT])) {
            $this->sort = Yii::$app->getModule('stream')->settings->get('defaultSort', Stream::SORT_CREATED_AT);
        }
    }

    /**
     * Makes sure a valid $from contentId field is set.
     */
    protected function checkFrom()
    {
        if (empty($this->from)) {
            $this->from = null;
        } else {
            $this->from = (int)$this->from;
        }
    }

    /**
     * Makes sure a valid $to contentId field is set.
     */
    protected function checkTo()
    {
        if (empty($this->to)) {
            $this->to = null;
        } else {
            $this->to = (int)$this->to;
        }
    }

    /**
     * Sets the default limit in case no limit has been set manually.
     */
    protected function checkLimit()
    {
        if (empty($this->limit)) {
            $this->limit = self::MAX_LIMIT;
        } elseif (Yii::$app->request->isConsoleRequest) {
            $this->limit = (int)$this->limit;
        } else {
            $this->limit = ($this->limit > self::MAX_LIMIT) ? self::MAX_LIMIT : (int)$this->limit;
        }
    }

    /**
     * Sets up the main query and stream order.
     */
    protected function setupCriteria()
    {
        $this->_query
            ->joinWith('createdBy')
            ->joinWith('contentContainer')
            ->limit($this->limit);

        if (!Yii::$app->getModule('stream')->showDeactivatedUserContent) {
            $this->_query->andWhere(['user.status' => User::STATUS_ENABLED]);
        }

        if ($this->contentId) {
            $this->_query->andWhere(['content.id' => $this->contentId]);
            return;
        }

        /**
         * Setup Sorting
         */
        /**
         * Setup Sorting
         */
        if ($this->sort == Stream::SORT_UPDATED_AT) {
            $this->_query->orderBy('content.stream_sort_date DESC');
            if (!empty($this->from)) {
                $this->_query->andWhere(
                    ['or',
                        "content.stream_sort_date < (SELECT stream_sort_date FROM content wd WHERE wd.id=:from)",
                        ['and',
                            "content.stream_sort_date = (SELECT stream_sort_date FROM content wd WHERE wd.id=:from)",
                            "content.id > :from",
                        ],
                    ],
                    [':from' => $this->from],
                );
            } elseif (!empty($this->to)) {
                $this->_query->andWhere(
                    ['or',
                        "content.stream_sort_date > (SELECT stream_sort_date FROM content wd WHERE wd.id=:to)",
                        ['and',
                            "content.stream_sort_date = (SELECT stream_sort_date FROM content wd WHERE wd.id=:to)",
                            "content.id < :to",
                        ],
                    ],
                    [':to' => $this->to],
                );
            }
        } else {
            $this->_query->orderBy('content.created_at DESC,content.id DESC');
            if (!empty($this->from)) {
                $this->_query->andWhere(
                    ['or',
                        "content.created_at < (SELECT created_at FROM content wd WHERE wd.id=:from)",
                        ['and',
                            "content.created_at = (SELECT created_at FROM content wd WHERE wd.id=:from)",
                            "content.id < :from",
                        ],
                    ],
                    [':from' => $this->from],
                );
            } elseif (!empty($this->to)) {
                $this->_query->andWhere(
                    ['or',
                        "content.created_at > (SELECT created_at FROM content wd WHERE wd.id=:to)",
                        ['and',
                            "content.created_at = (SELECT created_at FROM content wd WHERE wd.id=:to)",
                            "content.id > :to",
                        ],
                    ],
                    [':to' => $this->to],
                );
            }
        }

    }

    /**
     * Sets up and apply filters.
     *
     * @throws InvalidConfigException
     */
    protected function setupFilters()
    {
        $this->beforeApplyFilters();

        foreach (array_keys($this->filterHandlers) as $i) {
            $this->prepareHandler($this->filterHandlers[$i])->apply();
        }

        $this->afterApplyFilters();
    }

    /**
     * Is called right before applying query filters.
     *
     * Subclasses may use this function to add additional filters or modify existing filters.
     *
     * ```php
     * protected function beforeApplyFilters()
     * {
     *   $this->removeFilterHandler(SomeStreamFilter::class);
     *   $this->addFilterHandler(MyStreamFilter::class);
     *   // NOTE: Put the parent method here in the end of this method in order to have
     *   //       all core applied filters when external event triggers will be called
     *   parent::beforeApplyFilters();
     * }
     * ```
     *
     * @throws InvalidConfigException
     * @since 1.6
     */
    protected function beforeApplyFilters()
    {
        $this->trigger(static::EVENT_BEFORE_FILTER);
    }

    /**
     * Is called right after applying query filters.
     *
     * @throws InvalidConfigException
     * @since 1.6
     */
    protected function afterApplyFilters()
    {
        $this->trigger(static::EVENT_AFTER_FILTER);
    }

    /**
     * Adds a new filter handler to this query. This function accepts either a class name string or handler instance.
     * Note, this function will automatically set the following filter properties:
     *
     *   - streamQuery: this streamQuery instance
     *   - query: this activeQuery
     *   - formName: this formName usually StreamQuery
     *
     * Usage:
     *
     * ```php
     * // Use this if you need to set some initial filter data
     * $filter = $streamQuery->addFilterHandler(new MyFilterHandler(['container' => $this->container]))
     *
     * // or just
     * $filter = $streamQuery->addFilterHandler(AnotherFilter::class)
     * ```
     *
     * @param string|StreamQueryFilter $handler
     * @param bool $overwrite whether or not to overwrite existing filters of the same class
     * @return StreamQueryFilter|null initialized stream filter
     * @throws InvalidConfigException
     * @since 1.6
     */
    public function addFilterHandler($handler, $overwrite = true, $prepend = false)
    {
        if ($this->isExcludedFilterHandler($handler)) {
            return null;
        }

        if ($overwrite) {
            $this->removeFilterHandler($handler, false);
        }

        $handler = $this->prepareHandler($handler);

        /**
         * Some Filters must be prepended, when other filters rely on them.
         * E.g. `ContentContainerStreamFilter` is required for `DraftContentStreamFilter` which clones
         * the current query to get all drafts (for ContentContainer or Dashboard)
         */
        if ($prepend) {
            array_unshift($this->filterHandlers, $handler);
        } else {
            $this->filterHandlers[] = $handler;
        }
        return $handler;
    }

    /**
     * Can be used to add multiple filter handlers at once.
     *
     * @param $handlers
     * @param bool $overwrite
     * @return string[]|StreamQueryFilter[]
     * @throws InvalidConfigException
     * @see self::addFilterHandler
     */
    public function addFilterHandlers($handlers, $overwrite = true)
    {
        $result = [];
        foreach ($handlers as $handler) {
            $handler = $this->addFilterHandler($handler, $overwrite);
            if ($handler !== null) {
                $result[] = $handler;
            }
        }

        return $result;
    }

    /**
     * Can be used to remove filters by filter class.
     *
     * @param string|StreamQueryFilter $handlerToRemove
     * @param bool $exclude Exclude from further adding
     * @since 1.6
     */
    public function removeFilterHandler($handlerToRemove, bool $exclude = true)
    {
        $result = [];
        $handlerToRemoveClass = is_string($handlerToRemove) ? $handlerToRemove : get_class($handlerToRemove);
        foreach ($this->filterHandlers as $handler) {
            if (!is_a($handler, $handlerToRemoveClass, true)) {
                $result[] = $handler;
            }
        }

        $this->filterHandlers = $result;

        if ($exclude && !$this->isExcludedFilterHandler($handlerToRemoveClass)) {
            $this->excludedFilterHandlers[] = $handlerToRemoveClass;
        }
    }

    /**
     * Check the filter handler is excluded
     *
     * @param string|StreamQueryFilter $handler
     * @return bool
     */
    public function isExcludedFilterHandler($handler): bool
    {
        $handler = is_string($handler) ? $handler : get_class($handler);
        return in_array($handler, $this->excludedFilterHandlers);
    }

    /**
     * Can be used to search for a filter handler by class.
     *
     * @param string|StreamQueryFilter $class
     * @return StreamQueryFilter|null
     * @throws InvalidConfigException
     * @since 1.6
     */
    public function getFilterHandler($class): ?StreamQueryFilter
    {
        $handlerClass = is_string($class) ? $class : get_class($class);
        foreach ($this->filterHandlers as $handler) {
            if (is_a($handler, $handlerClass, true)) {
                return $this->prepareHandler($handler);
            }
        }

        return null;
    }

    /**
     * @param StreamQueryFilter|string $handler
     * @return StreamQueryFilter
     * @throws InvalidConfigException
     * @since 1.6
     */
    private function prepareHandler(&$handler)
    {
        if (is_string($handler)) {
            $handler = Yii::createObject([
                'class' => $handler,
                'streamQuery' => $this,
                'query' => $this->_query,
                'formName' => $this->formName(),
            ]);
        } elseif ($handler instanceof StreamQueryFilter) {
            $handler->streamQuery = $this;
            $handler->query = $this->_query;
            $handler->formName = $this->formName();
        } else {
            throw new InvalidConfigException('Invalid stream filter class');
        }

        return $handler;
    }

    /**
     * @return bool true of this query is used to query a single content entry
     */
    public function isSingleContentQuery()
    {
        return $this->limit == 1 || $this->contentId != null;
    }

    /**
     * Is inital stream requests (show first stream content)
     *
     * @return bool Whether or not this query is considered as initial stream query.
     */
    public function isInitialQuery()
    {
        return $this->from === null && $this->to === null && !$this->isSingleContentQuery();
    }
}
