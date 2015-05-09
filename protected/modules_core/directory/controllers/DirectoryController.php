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
        $keyword = Yii::app()->request->getParam('keyword', "");
        $page = (int) Yii::app()->request->getParam('page', 1);
        $_GET['keyword'] = $keyword; // Fix for post var

        $searchResultSet = Yii::app()->search->find($keyword, [
            'model' => 'User',
            'page' => $page,
            'pageSize' => HSetting::Get('paginationSize')
        ]);

        // Create Pagination Class
        $pagination = new CPagination($searchResultSet->total);
        $pagination->setPageSize($searchResultSet->pageSize);

        // Add Member Statistic Sidebar
        Yii::app()->interceptor->preattachEventHandler('DirectorySidebarWidget', 'onInit', function($event) {
            $event->sender->addWidget('application.modules_core.directory.widgets.NewMembersWidget', array(), array('sortOrder' => 10));
            $event->sender->addWidget('application.modules_core.directory.widgets.MemberStatisticsWidget', array(), array('sortOrder' => 20));
        });

        $this->render('members', array(
            'keyword' => $keyword,
            'users' => $searchResultSet->getResultInstances(),
            'pagination' => $pagination
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

        $keyword = Yii::app()->request->getParam('keyword', "");
        $page = (int) Yii::app()->request->getParam('page', 1);
        $_GET['keyword'] = $keyword; // Fix for post var

        $searchResultSet = Yii::app()->search->find($keyword, [
            'model' => 'Space',
            'page' => $page,
            'sortField' => ($keyword == '') ? 'title' : null,
            'pageSize' => HSetting::Get('paginationSize')
        ]);

        // Create Pagination Class
        $pagination = new CPagination($searchResultSet->total);
        $pagination->setPageSize($searchResultSet->pageSize);

        // Add Space Statistic Sidebar
        Yii::app()->interceptor->preattachEventHandler('DirectorySidebarWidget', 'onInit', function($event) {
            $event->sender->addWidget('application.modules_core.directory.widgets.NewSpacesWidget', array(), array('sortOrder' => 10));
            $event->sender->addWidget('application.modules_core.directory.widgets.SpaceStatisticsWidget', array(), array('sortOrder' => 20));
        });

        $this->render('spaces', array(
            'keyword' => $keyword,
            'spaces' => $searchResultSet->getResultInstances(),
            'pagination' => $pagination
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
        $this->render('userPosts', array());
    }

}
