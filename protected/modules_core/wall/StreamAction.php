<?php

/**
 * StreamAction returns a list of wall entries.
 *
 * *** DEPRECATED since 0.11 use BaseStreamAction / ContentContainerStreamAction instead! ***
 * 
 * @package humhub.modules_core.wall
 * @since 0.5
 * @author Luke
 * @deprecated since version 0.11
 */
class StreamAction extends CAction
{

    /**
     * Constants used for sorting
     */
    const SORT_CREATED_AT = 1;
    const SORT_UPDATED_AT = 2;

    /**
     * Modes
     */
    const MODE_NORMAL = "normal";
    const MODE_ACTIVITY = "activity";

    /**
     * @var String Type of wall output (normal or activity). Default is 'normal'.
     */
    public $mode = "normal";

    /**
     * @var type string type of the stream. (user, space, dashboard, community)
     */
    public $type = "";

    /**
     * @var type string guid of the user or space
     */
    private $typeGuid = ""; // user or space guid

    /**
     * @var integer is the id of the first wall entry to load
     */
    protected $wallEntryFrom;

    /**
     * @var integer is the id of last wall entry to load
     */
    protected $wallEntryTo; // id of last wallentry

    /**
     * @var int is the maximum of returned wall entries
     */
    public $wallEntryLimit; // limit of returned entries

    /**
     * @var integer is the unix timestamp of the max date of wall entry
     */
    public $wallEntryDateTo; // limit of returned entries to a max date

    /**
     * @var array of active filters
     */
    protected $filters = array();

    /**
     * @var string current sort mode
     */
    protected $sorting = StreamAction::SORT_CREATED_AT;

    /**
     * @var string where part of generated sql query
     */
    protected $sqlWhere = "";

    /**
     * @var string group by part of generated sql query
     */
    protected $sqlGroupBy = "";

    /**
     * @var string joins of generated sql query
     */
    protected $sqlJoin = "";

    /**
     * @var array of required sql parameters
     */
    protected $sqlParams = array();

    /**
     * @var integer current user id, used for console application
     */
    public $userId;

    /**
     * @var integer wall id of current user
     */
    public $userWallId;

    /**
     * Inits the stream action
     *
     * (When called from console, this method is not called.)
     */
    public function init()
    {

        Yii::beginProfile('initStreamAction');

        if (!Yii::app() instanceof CConsoleApplication) {

            // Define which stream we shall display?
            $this->type = Yii::app()->request->getParam('type');
            $this->typeGuid = Yii::app()->request->getParam('guid');

            if ($this->type != Wall::TYPE_DASHBOARD && $this->type != Wall::TYPE_USER && $this->type != Wall::TYPE_COMMUNITY && $this->type != Wall::TYPE_SPACE) {
                throw new CHttpException(500, 'Invalid wall type!');
            }

            // Options how many items
            $this->wallEntryFrom = (int) Yii::app()->request->getParam('from');
            $this->wallEntryTo = (int) Yii::app()->request->getParam('to');
            $this->wallEntryLimit = (int) Yii::app()->request->getParam('limit', 2);

            // Sorting (switch to updated at)
            if (Yii::app()->request->getParam('sort') == 'u') {
                $this->sorting = self::SORT_UPDATED_AT;
            }

            // Fill filter array
            foreach (explode(',', Yii::app()->request->getParam('filters', "")) as $filter) {
                $this->filters[] = trim($filter);
            }

            $this->userId = Yii::app()->user->id;
            $this->userWallId = Yii::app()->user->getModel()->wall_id;
            if ($this->userWallId == "")
                $this->userWallId = 0;
        }

        Yii::endProfile('initStreamAction');
    }

    /**
     * Console Stream Action
     * Used for generate daily report mails
     *
     * @return type
     */
    public function runConsole()
    {
        $this->init();
        $this->prepareSQL();
        $this->setupFilterSQL();

        $order = "ORDER BY wall_entry.id DESC";
        if ($this->sorting == self::SORT_UPDATED_AT) {
            $order = "ORDER BY wall_entry.updated_at DESC";
        }

        $sql = "SELECT wall_entry.*
			FROM wall_entry
                        LEFT JOIN content ON wall_entry.content_id = content.id
                        LEFT JOIN user creator ON creator.id = content.created_by
			{$this->sqlJoin}
			WHERE creator.status = 1
			{$this->sqlWhere}
			{$this->sqlGroupBy}
            {$order}
			LIMIT {$this->wallEntryLimit}
		";

        // Execute SQL
        $entries = WallEntry::model()->with('content')->findAllBySql($sql, $this->sqlParams);

        // Save Wall Type
        Wall::$currentType = $this->type;

        $output = "";
        foreach ($entries as $entry) {
            $output .= $entry->content->getUnderlyingObject()->getMailOut();
        }

        $returnInfo = array();
        $returnInfo['output'] = $output;
        $returnInfo['counter'] = count($entries);

        return $returnInfo;
    }

    /**
     * Execute the Stream Action and returns a JSON output.
     */
    public function run()
    {

        $this->init();
        $this->prepareSQL();
        $this->setupFilterSQL();

        Yii::beginProfile('runStreamAction');

        $stickedFirstOrder = "";

        // Show sticked items?
        if (($this->type == Wall::TYPE_SPACE || $this->type == Wall::TYPE_USER) && $this->wallEntryLimit != 1) {
            if ($this->wallEntryFrom == "") {
                $stickedFirstOrder = "content.sticked DESC,";
            } else {
                $this->sqlWhere .= " AND (content.sticked != 1 OR content.sticked is NULL)";
            }
        }


        //$order = "ORDER BY ".$stickedFirstOrder."wall_entry.created_at DESC";
        $order = "ORDER BY " . $stickedFirstOrder . "wall_entry.id DESC";
        if ($this->sorting == self::SORT_UPDATED_AT) {
            $order = "ORDER BY " . $stickedFirstOrder . "wall_entry.updated_at DESC";
        }

        $sql = "SELECT wall_entry.*
			FROM wall_entry
                        LEFT JOIN content ON wall_entry.content_id = content.id
                        LEFT JOIN user creator ON creator.id = content.created_by
			{$this->sqlJoin}
			WHERE creator.status = 1
			{$this->sqlWhere}
			{$this->sqlGroupBy}
            {$order}
			LIMIT {$this->wallEntryLimit}
		";

        // Execute SQL
        $entries = WallEntry::model()->with('content')->findAllBySql($sql, $this->sqlParams);

        // Save Wall Type
        Wall::$currentType = $this->type;

        $output = "";
        $lastEntryId = "";
        $generatedWallEntryIds = array();

        foreach ($entries as $entry) {

            $underlyingObject = $entry->content->getUnderlyingObject();
            $user = $underlyingObject->content->user;

            $output .= Yii::app()->getController()->renderPartial(
                    'application.modules_core.wall.views.wallEntry', array(
                'entry' => $entry,
                'user' => $user,
                'mode' => $this->mode,
                'object' => $underlyingObject,
                'content' => $underlyingObject->getWallOut()
                    ), true
            );
            $generatedWallEntryIds[] = $entry->id;
            $lastEntryId = $entry->id;
        }

        // Fire JQuery Time AGO
        Yii::app()->clientScript->registerScript('timeago', '$(".time").timeago();');

        $pageOut = "";
        Yii::app()->clientScript->renderHead($pageOut);
        Yii::app()->clientScript->renderBodyBegin($pageOut);
        $pageOut .= $output;
        Yii::app()->clientScript->renderBodyEnd($pageOut);

        $json = array();
        $json['output'] = $pageOut;
        $json['lastEntryId'] = $lastEntryId;
        $json['counter'] = count($entries);
        $json['entryIds'] = $generatedWallEntryIds;


        Yii::endProfile('runStreamAction');
        echo CJSON::encode($json);

        Yii::app()->end();
    }

    /**
     * Prepares SQL Query
     *
     * @throws CHttpException
     */
    protected function prepareSQL()
    {

        /**
         * Build SQL
         */
        $this->sqlParams[':userId'] = $this->userId;
        #$this->sqlParams[':wallEntryFrom'] = $this->wallEntryFrom;
        // From specific wall entry
        if ($this->wallEntryFrom != "" && $this->wallEntryFrom != 0) {
            if ($this->sorting == self::SORT_CREATED_AT) {
                $this->sqlWhere .= " AND wall_entry.id < " . $this->wallEntryFrom . " ";
                //$this->sqlWhere  .= " AND wall_entry.created_at < (SELECT created_at FROM wall_entry wd WHERE wd.id=". $this->wallEntryFrom .")";
            } elseif ($this->sorting == self::SORT_UPDATED_AT) {
                // For Sorting by updated at
                $this->sqlWhere .= " AND wall_entry.updated_at < (SELECT updated_at FROM wall_entry wd WHERE wd.id=" . $this->wallEntryFrom . ")";
            }
        }

        // To specific wall entry
        #if ($this->wallEntryTo != "" && $this->wallEntryTo != 0) {
        #	$this->sqlWhere  .= " AND wall_entry.id < ". $this->wallEntryTo ." ";
        #}

        if ($this->mode == 'normal') {
            $this->sqlWhere .= " AND content.object_model != 'Activity'";
        } else {
            $this->sqlWhere .= " AND content.object_model = 'Activity'";

            # Dont show own activities
            $this->sqlJoin .= " LEFT JOIN activity ON content.object_id=activity.id AND content.object_model = 'Activity'";
            $this->sqlWhere .= " AND content.user_id != :userId ";
        }


        if ($this->type == Wall::TYPE_DASHBOARD && !Yii::app()->user->isGuest) {

            // In case of an space entry, we need some left join, to be able to verify that the user
            // has access to see this entry
            $this->sqlJoin .= "
					LEFT JOIN wall ON wall.id = wall_entry.wall_id
					LEFT JOIN space_membership ON
						wall.object_id = space_membership.space_id AND
						space_membership.user_id=:userId AND
						space_membership.status=" . SpaceMembership::STATUS_MEMBER . "
				";

            // Get all Wall Ids where the User is assigned to
            $usersWallId = $this->userWallId;
            $this->sqlWhere .= " AND wall_entry.wall_id IN (
						SELECT uf.wall_id FROM user_follow
							LEFT JOIN user uf ON uf.id=user_follow.object_id AND user_follow.object_model='User'
							WHERE user_follow.user_id=:userId AND uf.wall_id is NOT NULL
						UNION
						SELECT sf.wall_id FROM user_follow
							LEFT JOIN space sf ON sf.id=user_follow.object_id AND user_follow.object_model='Space'
							WHERE user_follow.user_id=:userId AND sf.wall_id IS NOT NULL
						UNION
						SELECT sm.wall_id FROM space_membership
							LEFT JOIN space sm ON sm.id=space_membership.space_id
							WHERE space_membership.user_id=:userId AND sm.wall_id IS NOT NULL
						UNION
						SELECT {$usersWallId}
				) ";

            // Check if user can see current wall entry
            // First Line: IS NULL when not a space entry, so we need no extra checks
            // Second Line: When Visibilty == private The User must have membership status 3
            // Third Line: When Visibilty == public
            $this->sqlWhere .= "
					AND  (
						(wall.object_model='user' AND content.visibility = 0 AND content.user_id = :userId) OR
						(wall.object_model='space' AND content.visibility = 0 AND space_membership.status = " . SpaceMembership::STATUS_MEMBER . ") OR
						(content.visibility = 1 OR content.visibility IS NULL)
					)
				";

            // Additionally Group Entries of Same Model && Instance (Only for Activites?)
            $sqlGroupBy = " GROUP BY content.object_model, content.object_id ";
        } elseif ($this->type == Wall::TYPE_DASHBOARD && Yii::app()->user->isGuest) {

            // Include public space posts when space allows guest access
            $this->sqlWhere .= " AND (wall_entry.wall_id IN ( SELECT wall_id FROM space WHERE space.visibility=" . Space::VISIBILITY_ALL . ") ";

            // Include user posts when users profile is not hidden for guests
            $this->sqlWhere .= " OR wall_entry.wall_id IN ( SELECT wall_id FROM user WHERE status=1 AND visibility=" . User::VISIBILITY_ALL . ")) ";

            $this->sqlWhere .= " AND content.visibility=" . Content::VISIBILITY_PUBLIC;
        } elseif ($this->type == Wall::TYPE_COMMUNITY) {

            $this->sqlWhere .= " AND wall_entry.wall_id IN (
						SELECT wall_id FROM user WHERE status=1 AND visibility=" . User::VISIBILITY_ALL . "
				) ";
        } elseif ($this->type == Wall::TYPE_SPACE) {

            $space = Space::model()->findByAttributes(array('guid' => $this->typeGuid));
            $this->sqlWhere .= " AND wall_entry.wall_id = " . $space->wall_id;

            // Only Public Posts, User is NOT member of this space
            # && !Yii::app()->user->isAdmin()
            if (!$space->isMember()) {
                $this->sqlWhere .= " AND content.visibility=" . Content::VISIBILITY_PUBLIC;
            }

            // Dont show content, when user is guest and content is not visible for all
            if ($space->visibility != Space::VISIBILITY_ALL && Yii::app()->user->isGuest) {
                $this->sqlWhere .= " AND 1=2";
            }
        } elseif ($this->type == Wall::TYPE_USER) {

            $user = User::model()->findByAttributes(array('guid' => $this->typeGuid));

            $wallId = $user->wall_id;
            if ($wallId == "")
                $wallId = 0;

            if ($user->id != Yii::app()->user->id) {
                $this->sqlWhere .= " AND content.visibility=" . Content::VISIBILITY_PUBLIC;
            }

            $this->sqlWhere .= " AND wall_entry.wall_id = " . $wallId;

            // Do not show messages, for private profiles
            if (Yii::app()->user->isGuest && $user->visibility == User::VISIBILITY_REGISTERED_ONLY) {
                $this->sqlWhere .= " AND 1=2";
            }
        } else {
            throw new CHttpException(500, 'Target unknown!');
        }
    }

    /**
     * Adds filters to the SQL Query
     */
    protected function setupFilterSQL()
    {

        if ($this->wallEntryDateTo != "") {
            $this->sqlParams[':maxDate'] = $this->wallEntryDateTo;
            $this->sqlWhere .= "AND wall_entry.created_at > :maxDate";
        }

        // Show only content with attached files
        if (in_array('entry_files', $this->filters)) {
            $this->sqlWhere .= " AND (SELECT id FROM file WHERE file.object_model=content.object_model AND file.object_id=content.object_id LIMIT 1) IS NOT NULL";
        }

        // Setup Post specific filters
        if (in_array('posts_links', $this->filters)) {
            $this->sqlJoin .= " LEFT JOIN post ON content.object_id=post.id AND content.object_model = 'Post'";
            if (in_array('posts_links', $this->filters)) {
                $this->sqlWhere .= " AND post.url is not null";
            }
        }


        // Only apply archived filter when we should load more than one entry
        if ($this->wallEntryLimit != 1) {

            if (!in_array('entry_archived', $this->filters)) {
                $this->sqlWhere .= " AND (content.archived != 1 OR content.archived IS NULL)";
            }
        }

        // Show only mine items
        if (in_array('entry_mine', $this->filters)) {
            $this->sqlWhere .= " AND content.created_by=:userId";
        }

        // Show only items where the current user is involed
        if (in_array('entry_userinvoled', $this->filters)) {
            $this->sqlJoin .= " LEFT JOIN user_follow ON content.object_model=user_follow.object_model AND content.object_id=user_follow.object_id AND user_follow.user_id = :userId";
            $this->sqlWhere .= " AND user_follow.id IS NOT NULL";
        }

        // Posts only
        if (in_array('model_posts', $this->filters)) {
            $this->sqlWhere .= " AND content.object_model='Post'";
            
        }

        // Visibility filters
        if (in_array('visibility_private', $this->filters)) {
            $this->sqlWhere .= " AND content.visibility=" . Content::VISIBILITY_PRIVATE;
        }
        if (in_array('visibility_public', $this->filters)) {
            $this->sqlWhere .= " AND content.visibility=" . Content::VISIBILITY_PUBLIC;
        }
    }

}

?>
