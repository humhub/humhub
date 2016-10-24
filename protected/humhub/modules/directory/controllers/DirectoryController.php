<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\directory\controllers;

use Yii;
use humhub\modules\directory\widgets\Sidebar;

/**
 * Community/Directory Controller
 *
 * Shows all available users, group, spaces
 *
 * @package humhub.modules_core.directory.controllers
 * @since 0.5
 */
class DirectoryController extends \humhub\modules\directory\components\Controller
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setActionTitles([
            'members' => Yii::t('DirectoryModule.base', 'Members'),
            'spaces' => Yii::t('AdminModule.base', 'Spaces'),
            'user-posts' => Yii::t('AdminModule.base', 'User posts'),
        ]);
        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'guestAllowedActions' => ['groups', 'index', 'members', 'spaces', 'user-posts', 'stream']
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'stream' => [
                'class' => \humhub\modules\directory\components\UserPostsStreamAction::className(),
                'mode' => \humhub\modules\directory\components\UserPostsStreamAction::MODE_NORMAL,
            ],
        ];
    }

    /**
     * Index Action, redirects to member actions
     */
    public function actionIndex()
    {
        if ($this->module->isGroupListingEnabled()) {
            return $this->redirect(['groups']);
        } else {
            return $this->redirect(['members']);
        }
    }

    /**
     * Action for the members section of the directory
     *
     * @todo Dont pass lucene hits to view, build user array inside of action
     */
    public function actionMembers()
    {
        $keyword = Yii::$app->request->get('keyword', "");
        $page = (int) Yii::$app->request->get('page', 1);
        $groupId = (int) Yii::$app->request->get('groupId', "");

        $group = null;
        if ($groupId) {
            $group = \humhub\modules\user\models\Group::findOne(['id' => $groupId, 'show_at_directory' => 1]);
        }

        $searchOptions = [
            'model' => \humhub\modules\user\models\User::className(),
            'page' => $page,
            'pageSize' => $this->module->pageSize,
        ];

        if ($this->module->memberListSortField != "") {
            $searchOptions['sortField'] = $this->module->memberListSortField;
        }

        if ($group !== null) {
            $searchOptions['filters'] = ['groups' => $group->id];
        }

        $searchResultSet = Yii::$app->search->find($keyword, $searchOptions);

        $pagination = new \yii\data\Pagination(['totalCount' => $searchResultSet->total, 'pageSize' => $searchResultSet->pageSize]);

        \yii\base\Event::on(Sidebar::className(), Sidebar::EVENT_INIT, function($event) {
            $event->sender->addWidget(\humhub\modules\directory\widgets\NewMembers::className(), [], ['sortOrder' => 10]);
            $event->sender->addWidget(\humhub\modules\directory\widgets\MemberStatistics::className(), [], ['sortOrder' => 20]);
        });

        return $this->render('members', array(
                    'keyword' => $keyword,
                    'group' => $group,
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
        $keyword = Yii::$app->request->get('keyword', "");
        $page = (int) Yii::$app->request->get('page', 1);

        $searchResultSet = Yii::$app->search->find($keyword, [
            'model' => \humhub\modules\space\models\Space::className(),
            'page' => $page,
            'sortField' => ($keyword == '') ? 'title' : null,
            'pageSize' => $this->module->pageSize,
        ]);

        $pagination = new \yii\data\Pagination(['totalCount' => $searchResultSet->total, 'pageSize' => $searchResultSet->pageSize]);

        \yii\base\Event::on(Sidebar::className(), Sidebar::EVENT_INIT, function($event) {
            $event->sender->addWidget(\humhub\modules\directory\widgets\NewSpaces::className(), [], ['sortOrder' => 10]);
            $event->sender->addWidget(\humhub\modules\directory\widgets\SpaceStatistics::className(), [], ['sortOrder' => 20]);
        });

        return $this->render('spaces', array(
                    'keyword' => $keyword,
                    'spaces' => $searchResultSet->getResultInstances(),
                    'pagination' => $pagination,
        ));
    }

    /**
     * Group Section of the directory
     *
     * Shows a list of all groups in the application.
     */
    public function actionGroups()
    {
        if (!$this->module->isGroupListingEnabled()) {
            return $this->redirect(['members']);
        }

        $groups = \humhub\modules\user\models\Group::getDirectoryGroups();

        \yii\base\Event::on(Sidebar::className(), Sidebar::EVENT_INIT, function($event) {
            $event->sender->addWidget(\humhub\modules\directory\widgets\GroupStatistics::className(), [], ['sortOrder' => 10]);
        });

        return $this->render('groups', array(
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
        return $this->render('userPosts', array());
    }

}
