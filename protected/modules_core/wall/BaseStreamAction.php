<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * BaseStreamAction
 *
 * @package humhub.modules_core.wall
 * @author luke
 * @since 0.11
 */
class BaseStreamAction extends CAction
{

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
    public $filters = array();

    /**
     * @var CDbCriteria
     */
    public $criteria;

    /**
     * Optional stream user 
     * if no user is specified, the current logged in user will be used.
     * 
     * @var User
     */
    public $user = null;

    public function init()
    {

        $this->criteria = new CDbCriteria();

        // If no user is set, take current if logged in
        if (!Yii::app()->user->isGuest && $this->user == null) {
            $this->user = Yii::app()->user->getModel();
        }

        if ($this->user != null) {
            $this->criteria->params[':userId'] = $this->user->id;
        }

        // Read parameters
        $from = Yii::app()->request->getParam('from', 0);
        if ($from != 0) {
            $this->from = $from;
        }
        $sort = Yii::app()->request->getParam('sort', '');
        if ($sort != "") {
            $this->sort = $sort;
        }
        $limit = Yii::app()->request->getParam('limit', '');
        if ($limit != "" && $limit <= self::MAX_LIMIT) {
            $this->limit = $limit;
        }
        $mode = Yii::app()->request->getParam('mode', '');
        if ($mode != "" && ($mode == self::MODE_ACTIVITY || $mode == self::MODE_NORMAL)) {
            $this->mode = $mode;
        }
        foreach (explode(',', Yii::app()->request->getParam('filters', "")) as $filter) {
            $this->filters[] = trim($filter);
        }

        $this->setupCriteria();
        $this->setupFilters();
    }

    public function setupCriteria()
    {
        $this->criteria->alias = 'wall_entry';
        $this->criteria->join = 'LEFT JOIN content ON wall_entry.content_id = content.id';
        $this->criteria->join .= ' LEFT JOIN user creator ON creator.id = content.created_by';

        $this->criteria->limit = $this->limit;
        $this->criteria->condition = 'creator.status=' . User::STATUS_ENABLED;

        /**
         * Handle Stream Mode (Normal Stream or Activity Stream)
         */
        if ($this->mode == self::MODE_ACTIVITY) {
            $this->criteria->condition .= " AND content.object_model = 'Activity'";
            # Dont show own activities
            if ($this->user != null) {
                $this->criteria->join .= " LEFT JOIN activity ON content.object_id=activity.id AND content.object_model = 'Activity'";
                $this->criteria->condition .= " AND content.user_id != :userId ";
            }
        } else {
            $this->criteria->condition .= " AND content.object_model != 'Activity'";
        }

        /**
         * Setup Sorting
         */
        if ($this->sort == self::SORT_UPDATED_AT) {
            $this->criteria->order = 'wall_entry.updated_at DESC';
            if ($this->from != "")
                $this->criteria->condition .= " AND wall_entry.updated_at < (SELECT updated_at FROM wall_entry wd WHERE wd.id=" . $this->from . ")";
        } else {
            $this->criteria->order = 'wall_entry.id DESC';
            if ($this->from != "")
                $this->criteria->condition .= " AND wall_entry.id < " . $this->from . " ";
        }
    }

    /**
     * Setup additional filters
     */
    public function setupFilters()
    {
        if (in_array('entry_files', $this->filters)) {
            $fileSubSelect = Yii::app()->db->createCommand()
                    ->select('id')
                    ->from('file')
                    ->where("file.object_model=content.object_model AND file.object_id=content.object_id")
                    ->limit(1)
                    ->getText();
            $this->criteria->condition .= " AND (" . $fileSubSelect . ") IS NOT NULL";
        }

        // Setup Post specific filters
        if (in_array('posts_links', $this->filters)) {
            $this->criteria->join .= " LEFT JOIN post ON content.object_id=post.id AND content.object_model = 'Post'";
            if (in_array('posts_links', $this->filters)) {
                $this->criteria->condition .= " AND post.url is not null";
            }
        }

        // Only apply archived filter when we should load more than one entry
        if ($this->limit != 1) {
            if (!in_array('entry_archived', $this->filters)) {
                $this->criteria->condition .= " AND (content.archived != 1 OR content.archived IS NULL)";
            }
        }

        // Show only mine items
        if (in_array('entry_mine', $this->filters)) {
            $this->criteria->condition .= " AND content.created_by=:userId";
        }

        // Show only items where the current user is involed
        if (in_array('entry_userinvoled', $this->filters) && $this->user != null) {
            $this->criteria->join .= " LEFT JOIN user_follow ON content.object_model=user_follow.object_model AND content.object_id=user_follow.object_id AND user_follow.user_id = :userId";
            $this->criteria->condition .= " AND user_follow.id IS NOT NULL";
        }

        // Show only items where the current user is involed
        if (in_array('model_posts', $this->filters)) {
            $this->criteria->condition .= " AND content.object_model='Post'";
        }
        // Show only items where the current user is involed
        if (in_array('model_posts', $this->filters)) {
            $this->criteria->condition .= " AND content.object_model='Post'";
        }

        // Visibility filters
        if (in_array('visibility_private', $this->filters)) {
            $this->criteria->condition .= " AND content.visibility=" . Content::VISIBILITY_PRIVATE;
        }
        if (in_array('visibility_public', $this->filters)) {
            $this->criteria->condition .= " AND content.visibility=" . Content::VISIBILITY_PUBLIC;
        }
    }

    public function getWallEntries()
    {
        return WallEntry::model()->findAll($this->criteria);
    }

    public function run()
    {
        $this->init();

        $entries = WallEntry::model()->findAll($this->criteria);

        $output = "";
        $generatedWallEntryIds = array();
        $lastEntryId = "";
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

        header('Content-type: application/json');
        echo CJSON::encode($json);
        Yii::app()->end();
    }

}
