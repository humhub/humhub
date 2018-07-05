<?php

namespace humhub\modules\stream\models;

use Yii;
use yii\base\Model;
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
     * Stream sorting default = SORT_CREATED_AT;
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
     * @var \yii\db\ActiveQuery 
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
            [['limit', 'from', 'contentId'], 'number'],
            [['sort'], 'safe']
        ];
    }

    /**
     * Static initializer.
     * 
     * @param array|string|int $includes either an array of ContentActiveRecord class names or single class name or single contentId.
     * @param array|string $excludes either an array of ContentActiveRecord class names or single class name to exclude from the query.
     * @return StreamQuery
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

    public function init()
    {
        $this->_query = Content::find();
        // Set default user after initialization so it's avialable without assambling the query.
        $this->checkUser();
    }

    public function content($contentId)
    {
        if (!is_int($contentId)) {
            $this->contentId = $contentId;
        }
        return $this;
    }

    public function forUser($user = null)
    {
        $this->user = $user;
        $this->checkUser();
        return $this;
    }

    public function filters($filters = [])
    {
        $this->filters = (is_string($filters)) ? [$filters] : $filters;
        return $this;
    }

    public function addFilter($filters)
    {
        if (!is_string($filters)) {
            $this->filters[] = $filters;
        } elseif (is_array($filters)) {
            $this->filters = ArrayHelper::merge($this->filters, $filters);
        }
        return $this;
    }

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

    public function originator($user)
    {
        $this->originator = $user;
        return $this;
    }

    public function from($from = 0)
    {
        $this->from = $from;
        return $this;
    }

    public function limit($limit = self::MAX_LIMIT)
    {
        $this->limit = $limit;
        return $this;
    }

    public function filter($limit = self::MAX_LIMIT)
    {
        $this->limit = $limit;
        return $this;
    }

    public function query($build = false)
    {
        if ($build) {
            $this->setupQuery();
        }

        return $this->_query;
    }

    public function all()
    {
        if (!$this->_built) {
            $this->setupQuery();
        }

        return $this->_query->all();
    }

    protected function setupQuery()
    {
        $this->checkUser();
        $this->checkSort();
        $this->checkLimit();
        $this->checkFrom();
        $this->setupCriteria();
        $this->setupFilters();

        if (!empty($this->channel)) {
            $this->channel($this->channel);
        }

        $this->_built = true;
    }

    protected function checkUser()
    {
        if ($this->user === null && !Yii::$app->user->isGuest) {
            $this->user = Yii::$app->user->getIdentity();
        }
    }

    protected function checkSort()
    {
        if(empty($this->sort) || !in_array($this->sort, [Stream::SORT_CREATED_AT, Stream::SORT_UPDATED_AT])) {
           $this->sort = Yii::$app->getModule('stream')->settings->get('defaultSort', Stream::SORT_CREATED_AT);
        }
    }

    protected function checkFrom()
    {
        if (empty($this->from)) {
            $this->from = null;
        } else {
            $this->from = (int) $this->from;
        }
    }

    protected function checkLimit()
    {
        if (empty($this->limit) || $this->limit > self::MAX_LIMIT) {
            $this->limit = self::MAX_LIMIT;
        } else {
            $this->limit = (int) $this->limit;
        }
    }

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
                        "content.stream_sort_date < (SELECT updated_at FROM content wd WHERE wd.id=:from)",
                        ['and',
                            "content.stream_sort_date = (SELECT updated_at FROM content wd WHERE wd.id=:from)",
                            "content.id > :from"
                        ],
                    ], [':from' => $this->from]);
            }
        } else {
            $this->_query->orderBy('content.id DESC');
            if (!empty($this->from)) {
                $this->_query->andWhere("content.id < :from", [':from' => $this->from]);
            }
        }
    }

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

    public function isSingleContentQuery()
    {
        return $this->limit == 1 || $this->contentId != null;
    }

    /**
     * Sets the channel for this stream query
     * 
     * @param string $channel
     * @return StreamQuery
     */
    public function channel($channel)
    {
        $this->channel = $channel;
        $this->_query->andWhere(['content.stream_channel' => $channel]);
        return $this;
    }

}
