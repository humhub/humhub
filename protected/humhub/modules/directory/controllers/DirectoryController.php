<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\directory\controllers;

use Yii;
use yii\helpers\Url;
use humhub\models\Setting;
use humhub\modules\directory\widgets\Sidebar;
use yii\web\HttpException;

/**
 * Community/Directory Controller
 *
 * Shows all available users, group, spaces
 *
 * @package humhub.modules_core.directory.controllers
 * @since 0.5
 */
class DirectoryController extends \humhub\components\Controller
{

    /**
     * @inheritdoc
     */
    public $subLayout = "@humhub/modules/directory/views/directory/_layout";

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

        if (\humhub\modules\user\models\Group::find()->count() > 1)
            $this->redirect(Url::to(['groups']));
        else
            $this->redirect(Url::to(['members']));
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
        //$_GET['keyword'] = $keyword; // Fix for post var

        $searchResultSet = Yii::$app->search->find($keyword, [
            'model' => \humhub\modules\user\models\User::className(),
            'page' => $page,
            'pageSize' => Setting::Get('paginationSize')
        ]);

        $pagination = new \yii\data\Pagination(['totalCount' => $searchResultSet->total, 'pageSize' => $searchResultSet->pageSize]);

        \yii\base\Event::on(Sidebar::className(), Sidebar::EVENT_INIT, function($event) {
            $event->sender->addWidget(\humhub\modules\directory\widgets\NewMembers::className(), [], ['sortOrder' => 10]);
            $event->sender->addWidget(\humhub\modules\directory\widgets\MemberStatistics::className(), [], ['sortOrder' => 20]);
        });

        return $this->render('members', array(
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
        $spaceType = \humhub\modules\space\models\Type::findOne(['id' => Yii::$app->request->get('type_id')]);

        $keyword = Yii::$app->request->get('keyword', "");
        $page = (int) Yii::$app->request->get('page', 1);

        $searchResultSet = Yii::$app->search->find($keyword, [
            'model' => \humhub\modules\space\models\Space::className(),
            'page' => $page,
            'sortField' => ($keyword == '') ? 'title' : null,
            'pageSize' => Setting::Get('paginationSize'),
            'filters' => [
                'type_id' => ($spaceType !== null) ? $spaceType->id : ''
            ]
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
                    'spaceType' => $spaceType
        ));
    }

    /**
     * Group Section of the directory
     *
     * Shows a list of all groups in the application.
     */
    public function actionGroups()
    {
        $groups = \humhub\modules\user\models\Group::find()->all();

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
