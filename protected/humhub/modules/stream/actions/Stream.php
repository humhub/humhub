<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\stream\actions;

use Yii;
use yii\base\Action;
use humhub\modules\content\models\Content;
use humhub\modules\user\models\User;
use yii\base\ActionEvent;
use yii\base\Exception;

/**
 * Stream is the basic action for content streams.
 *
 * @author luke
 * @since 0.11
 */
abstract class Stream extends Action
{
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
     * Constants used for sorting
     */
    const SORT_CREATED_AT = 'c';
    const SORT_UPDATED_AT = 'u';

    /**
     * Modes
     */
    const MODE_NORMAL = "normal";
    const MODE_ACTIVITY = "activity";

    /**
     * Maximum wall entries per request
     */
    const MAX_LIMIT = 50;

    /**
     * @var string
     */
    public $mode;
    
    /**
     * Used to load single content entries.
     * @since 1.2
     */
    public $contentId;

    /**
     * First wall entry id to deliver
     *
     * @var int
     */
    public $from;

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
     * @var \yii\db\ActiveQuery
     */
    public $activeQuery;

    /**
     * Optional stream user
     * if no user is specified, the current logged in user will be used.
     *
     * @var User
     */
    public $user;

    /**
     * @inheritdocs
     */
    public function init()
    {
        $this->activeQuery = Content::find();

        // If no user is set, take current if logged in
        if ($this->user === null && !Yii::$app->user->isGuest) {
            $this->user = Yii::$app->user->getIdentity();
        }

        // Read parameters
        if (!Yii::$app->request->isConsoleRequest) {
            $this->contentId = Yii::$app->getRequest()->get('id');
            
            $from = Yii::$app->getRequest()->get('from', 0);
            if ($from != 0) {
                $this->from = (int) $from;
            }

            /**
             * Sorting
             */
            $sort = Yii::$app->getRequest()->get('sort', Yii::$app->getModule('content')->settings->get('stream.defaultSort'));
            if ($sort === static::SORT_CREATED_AT || $sort === static::SORT_UPDATED_AT) {
                $this->sort = $sort;
            } else {
                $this->sort = static::SORT_CREATED_AT;
            }

            $limit = Yii::$app->getRequest()->get('limit', '');
            if ($limit != "" && $limit <= self::MAX_LIMIT) {
                $this->limit = $limit;
            }
            
            $mode = Yii::$app->getRequest()->get('mode', '');
            if ($mode != "" && ($mode == self::MODE_ACTIVITY || $mode == self::MODE_NORMAL)) {
                $this->mode = $mode;
            }
            
            foreach (explode(',', Yii::$app->getRequest()->get('filters', "")) as $filter) {
                $this->filters[] = trim($filter);
            }
        }

        $this->setupCriteria();
        $this->setupFilters();
    }

    public function setupCriteria()
    {
        $this->activeQuery->joinWith('createdBy');
        $this->activeQuery->joinWith('contentContainer');

        $this->activeQuery->limit($this->limit);
        $this->activeQuery->andWhere(['user.status' => User::STATUS_ENABLED]);

        /**
         * Handle Stream Mode (Normal Stream or Activity Stream)
         */
        if ($this->mode == self::MODE_ACTIVITY) {
            $this->activeQuery->andWhere(['content.object_model' => \humhub\modules\activity\models\Activity::className()]);

            // Dont show own activities
            if ($this->user !== null) {
                $this->activeQuery->leftJoin('activity', 'content.object_id=activity.id AND content.object_model=:activityModel', ['activityModel' => \humhub\modules\activity\models\Activity::className()]);
                $this->activeQuery->andWhere('content.created_by != :userId', array(':userId' => $this->user->id));
            }
        } else {
            $this->activeQuery->andWhere(['!=', 'content.object_model', \humhub\modules\activity\models\Activity::className()]);
        }
        
        if($this->isSingleContentQuery()) {
            $this->activeQuery->andWhere(['content.id' => $this->contentId]);
            return;
        }

        /**
         * Setup Sorting
         */
        if ($this->sort == self::SORT_UPDATED_AT) {
            $this->activeQuery->orderBy('content.stream_sort_date DESC');
            if ($this->from != "") {
                $this->activeQuery->andWhere("content.stream_sort_date < (SELECT updated_at FROM content wd WHERE wd.id=" . $this->from . ")");
            }
        } else {
            $this->activeQuery->orderBy('content.id DESC');
            if ($this->from != "")
                $this->activeQuery->andWhere("content.id < " . $this->from);
        }
    }

    /**
     * Setup additional filters
     */
    public function setupFilters()
    {
        if (in_array('entry_files', $this->filters)) {
            $fileSelector = (new \yii\db\Query())
                    ->select(["id"])
                    ->from('file')
                    ->where('file.object_model=content.object_model AND file.object_id=content.object_id')
                    ->limit(1);
            $fileSelectorSql = Yii::$app->db->getQueryBuilder()->build($fileSelector)[0];

            $this->activeQuery->andWhere('(' . $fileSelectorSql . ') IS NOT NULL');
        }

        // Setup Post specific filters
        if (in_array('posts_links', $this->filters)) {
            $this->activeQuery->leftJoin('post', 'content.object_id=post.id AND content.object_model=:postModel', ['postModel' => \humhub\modules\post\models\Post::className()]);
            $this->activeQuery->andWhere("post.url is not null");
        }

        // Only apply archived filter when we should load more than one entry
        if ($this->limit != 1) {
            if (!in_array('entry_archived', $this->filters)) {
                $this->activeQuery->andWhere("(content.archived != 1 OR content.archived IS NULL)");
            }
        }
        
        // Show only mine items
        if (in_array('entry_mine', $this->filters) && $this->user !== null) {
            $this->activeQuery->andWhere(['content.created_by' => $this->user->id]);
        }
        
        // Show only items where the current user is involed
        if (in_array('entry_userinvoled', $this->filters) && $this->user !== null) {

            $this->activeQuery->leftJoin('user_follow', 'content.object_model=user_follow.object_model AND content.object_id=user_follow.object_id AND user_follow.user_id = :userId', ['userId' => $this->user->id]);
            $this->activeQuery->andWhere("user_follow.id IS NOT NULL");
        }
       
        if (in_array('model_posts', $this->filters)) {
            $this->activeQuery->andWhere(["content.object_model" => \humhub\modules\post\models\Post::className()]);
        }
        // Visibility filters
        if (in_array('visibility_private', $this->filters)) {
            $this->activeQuery->andWhere(['content.visibility' => Content::VISIBILITY_PRIVATE]);
        }
        if (in_array('visibility_public', $this->filters)) {
            $this->activeQuery->andWhere(['content.visibility' => Content::VISIBILITY_PUBLIC]);
        }
    }
    
    public function isSingleContentQuery() {
        return $this->contentId != null;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $output = [];

        $this->init();

        $output['content'] = [];

        foreach ($this->activeQuery->all() as $content) {
            $output['content'][$content->id] = static::getContentResultEntry($content);
        }
        $output['total'] = count($output['content']);
        $output['isLast'] = ($output['total'] < $this->activeQuery->limit);
        $output['contentOrder'] = array_keys($output['content']);


        return $output;
    }

    /**
     * Returns an array contains all informations required to display a content
     * in stream.
     * 
     * @param Content $content the content
     * @return array
     */
    public static function getContentResultEntry(Content $content)
    {
        $result = [];

        // Get Underlying Object (e.g. Post, Poll, ...)
        $underlyingObject = $content->getPolymorphicRelation();
        if ($underlyingObject === null) {
            throw new Exception('Could not get contents underlying object!');
        }
        $underlyingObject->populateRelation('content', $content);

        $result['output'] = Yii::$app->controller->renderAjax('@humhub/modules/content/views/layouts/wallEntry', [
            'entry' => $content,
            'user' => $underlyingObject->content->createdBy,
            'object' => $underlyingObject,
            'content' => $underlyingObject->getWallOut()
                ], true);
        
        $result['sticked'] = (boolean) $content->sticked;
        $result['archived'] = (boolean) $content->archived;
        $result['guid'] = $content->guid;
        $result['id'] = $content->id;

        return $result;
    }

    /**
     * Is inital stream requests (show first stream content)
     * 
     * @return boolean Is initial request
     */
    protected function isInitialRequest()
    {
        return ($this->from == '' && $this->limit != 1);
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
     * This method is called right after `run()` is executed.
     * You may override this method to do post-processing work for the action run.
     */
    protected function afterRun()
    {
        $event = new ActionEvent($this);
        $this->trigger(self::EVENT_AFTER_RUN, $event);
    }
}
