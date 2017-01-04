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
use humhub\modules\stream\models\StreamQuery;
use humhub\modules\user\models\User;
use yii\base\ActionEvent;
use yii\base\Exception;

/**
 * Stream is the basic action for content streams.
 *
 * JSON output structure:
 *      content             - array, content id is key
 *           id             - int, id of content 
 *           guid           - string, guid of contetn
 *           sticked        - boolean, is content sticked
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
     * 
     * @deprecated since version 1.2 use $streamQuery->query() instead
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
     * Used to filter the stream content entrie classes against a given array.
     * @var type 
     * @since 1.2
     */
    public $includes = [];

    /**
     * Used to filter our specific types
     * @var type 
     * @since 1.2
     */
    public $excludes = [];

    /**
     * Stream query model instance
     * @var type 
     * @since 1.2
     */
    protected $streamQuery;

    /**
     * @var string suppress similar content types in a row 
     */
    public $streamQueryClass = 'humhub\modules\stream\models\StreamSuppressQuery';

    /**
     * @inheritdocs
     */
    public function init()
    {
        $streamQueryClass = $this->streamQueryClass;
        $this->streamQuery = $streamQueryClass::find($this->includes, $this->excludes)->forUser($this->user);

        // Read parameters
        if (!Yii::$app->request->isConsoleRequest) {
            $this->streamQuery->load(Yii::$app->request->get());

            if (Yii::$app->getRequest()->get('mode', $this->mode) === self::MODE_ACTIVITY) {
                $this->streamQuery->includes(\humhub\modules\activity\models\Activity::className());
                $this->streamQuery->query()->leftJoin('activity', 'content.object_id=activity.id AND content.object_model=:activityModel', ['activityModel' => \humhub\modules\activity\models\Activity::className()]);
                // Note that if $this->user is null the streamQuery will use the current user identity!
                $this->streamQuery->query()->andWhere('content.created_by != :userId', [':userId' => $this->streamQuery->user->id]);
            }

            foreach (explode(',', Yii::$app->getRequest()->get('filters', "")) as $filter) {
                $this->streamQuery->addFilter(trim($filter));
            }
        }

        $this->setActionSettings();

        // Build query and set activeQuery.
        $this->activeQuery = $this->streamQuery->query(true);
        $this->user = $this->streamQuery->user;

        // Update action filters with merged request and configured action filters.
        $this->filters = $this->streamQuery->filters;

        // Append additional filter of subclasses.
        $this->setupCriteria();
        $this->setupFilters();
    }

    protected function setActionSettings()
    {
        // Merge configured filters set for this action with request filters.
        $this->streamQuery->addFilter($this->filters);

        // Overwrite limit if there was no setting in the request.
        if (empty($this->streamQuery->limit)) {
            $this->streamQuery->limit = $this->limit;
        }

        if (empty($this->streamQuery->sort)) {
            $this->streamQuery->sort = $this->sort;
        }
    }

    public function setupCriteria()
    {
        // Can be overwritten by subtypes to add additional criterias.
    }

    public function setupFilters()
    {
        // Can be overwritten by subtypes to add additional filters.
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

        foreach ($this->streamQuery->all() as $content) {
            try {
                $output['content'][$content->id] = static::getContentResultEntry($content);
            } catch (Exception $e) {
                // Don't kill the stream action in prod environments in case the rendering of an entry fails.
                if (YII_ENV_PROD) {
                    Yii::error($e);
                } else {
                    throw $e;
                }
            }
        }

        $output['total'] = count($output['content']);   //         // Required?
        $output['isLast'] = ($output['total'] < $this->activeQuery->limit);
        $output['contentOrder'] = array_keys($output['content']);
        $output['lastContentId'] = end($output['contentOrder']);

        if ($this->streamQuery instanceof \humhub\modules\stream\models\StreamSuppressQuery) {
            $output['contentSuppressions'] = $this->streamQuery->getSuppressions();
            $output['lastContentId'] = $this->streamQuery->getLastContentId();
        }

        return $output;
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
