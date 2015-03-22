<?php

/**
 * Community/Directory Controller
 *
 * Shows all available users, group, spaces
 *
 * @package humhub.modules_core.directory.controllers
 * @since 0.5
 */
class DirectoryController extends Controller
{

    public $subLayout = "_layout";

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow',
                'users' => array('@', (HSetting::Get('allowGuestAccess', 'authentication_internal')) ? "?" : "@"),
            ),
            array('deny',
                'users' => array('*'),
            ),
        );
    }

    public function actions()
    {
        return array(
            'stream' => array(
                'class' => 'application.modules_core.directory.UserPostsStreamAction',
                'mode' => BaseStreamAction::MODE_NORMAL,
            ),
        );
    }

    /**
     * Index Action, redirects to member actions
     */
    public function actionIndex()
    {

        if (Group::model()->count() > 1)
            $this->redirect($this->createUrl('groups'));
        else
            $this->redirect($this->createUrl('members'));
    }

    /**
     * Action for the members section of the directory
     *
     * @todo Dont pass lucene hits to view, build user array inside of action
     */
    public function actionMembers()
    {

        $keyword = Yii::app()->request->getParam('keyword', ""); // guid of user/workspace
        $page = (int) Yii::app()->request->getParam('page', 1); // current page (pagination)

        $keyword = Yii::app()->input->stripClean($keyword);

        $hits = array();
        $query = "";
        $hitCount = 0;

        // Build Lucene Query
        $query = "model:User";
        $sortField = null;
        if ($keyword != "") {
            $query .= " AND " . $keyword;
        } else {
            #$sortField = ' title';
        }

        // Execute Lucene Search
        $hits = new ArrayObject(HSearch::getInstance()->Find($query, $sortField));
        $hitCount = count($hits);

        // Limit Hits
        $hits = new LimitIterator($hits->getIterator(), ($page - 1) * HSetting::Get('paginationSize'), HSetting::Get('paginationSize'));

        // Create Pagination Class
        $pages = new CPagination($hitCount);
        $pages->setPageSize(HSetting::Get('paginationSize'));
        $_GET['keyword'] = $keyword; // Fix for post var
        // Add Meber Statistic Sidebar
        Yii::app()->interceptor->preattachEventHandler('DirectorySidebarWidget', 'onInit', function($event) {
            $event->sender->addWidget('application.modules_core.directory.widgets.NewMembersWidget', array(), array('sortOrder' => 10));
            $event->sender->addWidget('application.modules_core.directory.widgets.MemberStatisticsWidget', array(), array('sortOrder' => 20));
        });

        $this->render('members', array(
            'keyword' => $keyword, // current search keyword
            'hits' => $hits, // found hits
            'pages' => $pages, // CPagination
            'hitCount' => $hitCount, // number of hits
            'pageSize' => HSetting::Get('paginationSize'), // pagesize
        ));
    }

    /**
     * Space Section of directory
     *
     * Provides a list of all visible spaces.
     *
     * @todo Dont pass lucene hits to view, build user array inside of action
     */
    public function actionSpaces()
    {

        $keyword = Yii::app()->request->getParam('keyword', ""); // guid of user/workspace
        $page = (int) Yii::app()->request->getParam('page', 1); // current page (pagination)

        $keyword = Yii::app()->input->stripClean($keyword);

        $hits = array();
        $query = "";
        $hitCount = 0;


        $sortField = null;
        $query = "model:Space";
        if ($keyword != "") {
            $query .= " AND " . $keyword;
        } else {
            $sortField = 'title';
        }

        //$hits = new ArrayObject(
        //                HSearch::getInstance()->Find($query, HSetting::Get('paginationSize'), $page
        //        ));

        $hits = new ArrayObject(HSearch::getInstance()->Find($query, $sortField));

        $hitCount = count($hits);

        // Limit Hits
        $hits = new LimitIterator($hits->getIterator(), ($page - 1) * HSetting::Get('paginationSize'), HSetting::Get('paginationSize'));

        // Create Pagination Class
        $pages = new CPagination($hitCount);
        $pages->setPageSize(HSetting::Get('paginationSize'));
        $_GET['keyword'] = $keyword; // Fix for post var
        // Add Meber Statistic Sidebar
        Yii::app()->interceptor->preattachEventHandler('DirectorySidebarWidget', 'onInit', function($event) {
            $event->sender->addWidget('application.modules_core.directory.widgets.NewSpacesWidget', array(), array('sortOrder' => 10));
            $event->sender->addWidget('application.modules_core.directory.widgets.SpaceStatisticsWidget', array(), array('sortOrder' => 20));
        });

        $this->render('spaces', array(
            'keyword' => $keyword, // current search keyword
            'hits' => $hits, // found hits
            'pages' => $pages, // CPagination
            'hitCount' => $hitCount, // number of hits
            'pageSize' => HSetting::Get('paginationSize'), // pagesize
        ));
    }

    /**
     * Group Section of the directory
     *
     * Shows a list of all groups in the application.
     */
    public function actionGroups()
    {

        $groups = Group::model()->findAll();

        // Add Meber Statistic Sidebar
        Yii::app()->interceptor->preattachEventHandler('DirectorySidebarWidget', 'onInit', function($event) {
            $event->sender->addWidget('application.modules_core.directory.widgets.GroupStatisticsWidget', array(), array('sortOrder' => 10));
        });

        $this->render('groups', array(
            'groups' => $groups,
        ));
    }

    /**
     * User Posts
     *
     * Shows public all user posts inside a wall.
     *
     * @todo Add some statistics to the view
     */
    public function actionUserPosts()
    {

        /*
          // Stats
          $statsCountPosts = Post::model()->count();
          //$statsCountProfilePosts = Post::model()->count('space_id is null');
          $statsCountProfilePosts = 0;

          $statsCountComments = Comment::model()->count();
          $statsCountLikes = Like::model()->count();

          $statsUserTopPosts = User::model()->find('id = (SELECT created_by FROM post GROUP BY created_by ORDER BY count(*) DESC LIMIT 1)');
          $statsUserTopComments = User::model()->find('id = (SELECT created_by id  FROM comment GROUP BY created_by ORDER BY count(*) DESC LIMIT 1)');
          $statsUserTopLikes = User::model()->find('id = (SELECT created_by  FROM `like`  GROUP BY created_by ORDER BY count(*) DESC LIMIT 1)');

          $this->render('userPosts', array(
          'statsCountPosts' => $statsCountPosts,
          'statsCountProfilePosts' => $statsCountProfilePosts,
          'statsCountComments' => $statsCountComments,
          'statsCountLikes' => $statsCountLikes,
          'statsUserTopPosts' => $statsUserTopPosts,
          'statsUserTopComments' => $statsUserTopComments,
          'statsUserTopLikes' => $statsUserTopLikes,
          ));
         */

        $this->render('userPosts', array());
    }

}
