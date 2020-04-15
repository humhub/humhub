<?php

namespace humhub\modules\stream\models;

use Yii;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use humhub\modules\stream\actions\Stream;
use humhub\modules\stream\models\filters\ContentTypeStreamFilter;
use humhub\modules\stream\models\filters\DefaultStreamFilter;
use humhub\modules\stream\models\filters\OriginatorStreamFilter;
use humhub\modules\stream\models\filters\TopicStreamFilter;
use humhub\modules\ui\filter\models\QueryFilter;
use humhub\modules\content\models\Content;
use humhub\modules\user\models\User;

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
    const EVENT_BEFORE_FILTER = 'beforeFilter';

    /**
     * Default channels
     */
    const CHANNEL_DEFAULT = 'default';
    const CHANNEL_ACTIVITY = 'activity';

    /**
     * Maximum wall entries per request
     */
    const MAX_LIMIT = 20;

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
     * @var \humhub\modules\user\models\User
     */
    public $user;

    /**
     * Can be set to filter content of a specific user
     * @var \humhub\modules\user\models\User
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
    ];

    /**
     * The content query.
     *
     * @var ActiveQuery
     */
    protected $_query;

    /**
     * @var boolean query built
     */
    protected $_built = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['limit', 'from', 'to', 'contentId'], 'number'],
            [['sort'], 'safe']
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
     * @param $user|null User if null the current user identity will be used
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
        if (!is_string($filters)) {
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
        if ($build) {
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
        return $this->query(!$this->_built)->all();
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
        if(empty($this->sort) || !in_array($this->sort, [Stream::SORT_CREATED_AT, Stream::SORT_UPDATED_AT])) {
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
            $this->from = (int) $this->from;
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
            $this->to = (int) $this->to;
        }
    }

    /**
     * Sets the default limit in case no limit has been set manually.
     */
    protected function checkLimit()
    {
        if (empty($this->limit) || $this->limit > self::MAX_LIMIT) {
            $this->limit = self::MAX_LIMIT;
        } else {
            $this->limit = (int) $this->limit;
        }
    }

    /**
     * Sets up the main query and stream order.
     */
    protected function setupCriteria()
    {
        $this->_query->joinWith('createdBy');
        $this->_query->joinWith('contentContainer');

        $this->_query->limit($this->limit);

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
        if ($this->sort == Stream::SORT_UPDATED_AT) {
            $this->_query->orderBy('content.stream_sort_date DESC');
            if (!empty($this->from)) {
                $this->_query->andWhere(
                    ['or',
                        "content.stream_sort_date < (SELECT stream_sort_date FROM content wd WHERE wd.id=:from)",
                        ['and',
                            "content.stream_sort_date = (SELECT stream_sort_date FROM content wd WHERE wd.id=:from)",
                            "content.id > :from"
                        ],
                    ], [':from' => $this->from]);
            } elseif (!empty($this->to)) {
                $this->_query->andWhere(
                    ['or',
                        "content.stream_sort_date > (SELECT stream_sort_date FROM content wd WHERE wd.id=:to)",
                        ['and',
                            "content.stream_sort_date = (SELECT stream_sort_date FROM content wd WHERE wd.id=:to)",
                            "content.id < :to"
                        ],
                    ], [':to' => $this->to]);
            }
        } else {
            $this->_query->orderBy('content.id DESC');
            if (!empty($this->from)) {
                $this->_query->andWhere("content.id < :from", [':from' => $this->from]);
            } elseif (!empty($this->to)) {
                $this->_query->andWhere("content.id > :to", [':to' => $this->to]);
            }
        }
    }

    /**
     * Sets up the filter queries.
     *
     * @throws \yii\base\InvalidConfigException
     */
    protected function setupFilters()
    {
        $this->trigger(static::EVENT_BEFORE_FILTER);

        foreach ($this->filterHandlers as $handlerClass) {
            /** @var $handler QueryFilter **/
            Yii::createObject([
                'class' => $handlerClass,
                'streamQuery' => $this,
                'query' => $this->_query,
                'formName' => $this->formName()
            ])->apply();
        }
    }

    /**
     * @return bool true of this query is used to query a single content entry
     */
    public function isSingleContentQuery()
    {
        return $this->limit == 1 || $this->contentId != null;
    }
}
