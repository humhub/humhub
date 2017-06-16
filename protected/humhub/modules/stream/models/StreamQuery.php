<?php

namespace humhub\modules\stream\models;

use Yii;
use humhub\modules\content\models\Content;
use humhub\modules\user\models\User;

/**
 * Description of StreamQuery
 *
 * @author buddha
 * @since 1.2
 */
class StreamQuery extends \yii\base\Model
{

    /**
     * Constants used for sorting
     */
    const SORT_CREATED_AT = 'c';
    const SORT_UPDATED_AT = 'u';

    /**
     * Default filters
     */
    const FILTER_FILES = "entry_files";
    const FILTER_ARCHIVED = "entry_archived";
    const FILTER_MINE = "entry_mine";
    const FILTER_INVOLVED = "entry_userinvolved";
    const FILTER_PRIVATE = "visibility_private";
    const FILTER_PUBLIC = "visibility_public";

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
    protected $_includes;

    /**
     * @var string stream channel to display
     */
    protected $_channel;

    /**
     * Can be set to filter out specific content types.
     * 
     * @var array Content type filter
     */
    protected $_excludes;

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
     * @var array 
     */
    public $filters = [];

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
            [['filters', 'sort'], 'safe']
        ];
    }

    /**
     * Static initializer.
     * 
     * @param array|string|int $types either an array of ContentActiveRecord classnames or single classname or single contentId or null.
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
        $this->filters = (is_string($filters)) ? [$this->filters] : $this->filters;
        return $this;
    }

    public function addFilter($filters)
    {
        if (!is_string($filters)) {
            $this->filters[] = $filters;
        } else if (is_array($filters)) {
            $this->filters = \yii\helpers\ArrayHelper::merge($this->filters, $filters);
        }
        return $this;
    }

    public function includes($includes = [])
    {
        if (is_string($includes)) {
            $this->_includes = [$includes];
        } else if (is_array($includes)) {
            $this->_includes = $includes;
        }

        return $this;
    }

    public function excludes($types = [])
    {
        if (is_string($types)) {
            $this->_excludes = [$types];
        } else if (is_array($types)) {
            $this->_excludes = $types;
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

        if (empty($this->_channel)) {
            $this->channel(self::CHANNEL_DEFAULT);
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
        if(empty($this->sort) || !in_array($this->sort, [static::SORT_CREATED_AT, static::SORT_UPDATED_AT])) {
           $this->sort = Yii::$app->getModule('stream')->settings->get('defaultSort', static::SORT_CREATED_AT);
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
        if ($this->sort == self::SORT_UPDATED_AT) {
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
        $this->setOriginatorFilter();
        $this->setDefaultFilter();
        $this->setTypeFilter();
    }

    protected function setOriginatorFilter()
    {
        if ($this->originator && !in_array(self::FILTER_MINE, $this->filters)) {
            $this->_query->andWhere(['content.created_by' => $this->originator->id]);
        }
    }

    public function isFilter($filter)
    {
        return in_array($filter, $this->filters);
    }

    protected function setDefaultFilter()
    {
        if ($this->isFilter(self::FILTER_FILES)) {
            $this->filterFile();
        }

        // Only apply archived filter when we should load more than one entry
        if (!$this->isSingleContentQuery() && !$this->isFilter(self::FILTER_ARCHIVED)) {
            $this->unFilterArchived();
        }

        // Show only mine items
        if ($this->isFilter(self::FILTER_MINE)) {
            $this->filterMine();
        }

        // Show only items where the current user is invovled
        if ($this->isFilter(self::FILTER_INVOLVED)) {
            $this->filterInvolved();
        }

        // Visibility filters
        if ($this->isFilter(self::FILTER_PRIVATE)) {
            $this->filterPrivate();
        } else if ($this->isFilter(self::FILTER_PUBLIC)) {
            $this->filterPublic();
        }
    }

    protected function filterFile()
    {
        $fileSelector = (new \yii\db\Query())
                ->select(["id"])
                ->from('file')
                ->where('file.object_model=content.object_model AND file.object_id=content.object_id')
                ->limit(1);

        $fileSelectorSql = Yii::$app->db->getQueryBuilder()->build($fileSelector)[0];
        $this->_query->andWhere('(' . $fileSelectorSql . ') IS NOT NULL');
        return $this;
    }

    protected function unFilterArchived()
    {
        $this->_query->andWhere("(content.archived != 1 OR content.archived IS NULL)");
        return $this;
    }

    protected function filterMine()
    {
        if ($this->user) {
            $this->_query->andWhere(['content.created_by' => $this->user->id]);
        }
        return $this;
    }

    protected function filterInvolved()
    {
        if ($this->user) {
            $this->_query->leftJoin('user_follow', 'content.object_model=user_follow.object_model AND content.object_id=user_follow.object_id AND user_follow.user_id = :userId', ['userId' => $this->user->id]);
            $this->_query->andWhere("user_follow.id IS NOT NULL");
        }
        return $this;
    }

    protected function filterPublic()
    {
        $this->_query->andWhere(['content.visibility' => Content::VISIBILITY_PUBLIC]);
        return $this;
    }

    protected function filterPrivate()
    {
        $this->_query->andWhere(['content.visibility' => Content::VISIBILITY_PRIVATE]);
        return $this;
    }

    public function isSingleContentQuery()
    {
        return $this->limit == 1 || $this->contentId != null;
    }

    protected function setTypeFilter()
    {
        if (is_string($this->_includes)) {
            $this->_includes = [$this->_includes];
        }

        if (count($this->_includes) === 1) {
            $this->_query->andWhere(["content.object_model" => $this->_includes[0]]);
        } else if (!empty($this->_includes)) {
            $this->_query->andWhere(['IN', 'content.object_model', $this->_includes]);
        }

        if (is_string($this->_excludes)) {
            $this->_excludes = [$this->_excludes];
        }

        if (count($this->_excludes) === 1) {
            $this->_query->andWhere(['!=', "content.object_model", $this->_excludes[0]]);
        } else if (!empty($this->_excludes)) {
            $this->_query->andWhere(['NOT IN', 'content.object_model', $this->_excludes]);
        }
    }

    /**
     * Sets the channel for this stream query
     * 
     * @param string $channel
     * @return StreamQuery
     */
    public function channel($channel)
    {
        $this->_channel = $channel;
        $this->_query->andWhere(['content.stream_channel' => $channel]);
        return $this;
    }

}
