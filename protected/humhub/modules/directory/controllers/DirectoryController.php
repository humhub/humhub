<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\directory\controllers;

use humhub\modules\directory\components\UserPostsStreamAction;
use humhub\modules\directory\components\Controller;
use humhub\modules\directory\Module;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use humhub\modules\directory\widgets\Sidebar;
use humhub\modules\directory\widgets\NewMembers;
use humhub\modules\directory\widgets\MemberStatistics;
use humhub\modules\directory\widgets\NewSpaces;
use humhub\modules\directory\widgets\SpaceStatistics;
use humhub\modules\directory\widgets\GroupStatistics;
use yii\data\Pagination;
use yii\base\Event;
use Yii;
use yii\web\HttpException;

/**
 * Community/Directory Controller
 *
 * Shows all available users, group, spaces
 *
 * @property Module $module
 * @since 0.5
 */
class DirectoryController extends Controller
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
    public function actions()
    {
        return [
            'stream' => [
                'class' => UserPostsStreamAction::class,
                'mode' => UserPostsStreamAction::MODE_NORMAL,
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
     */
    public function actionMembers()
    {
        $keyword = (string)Yii::$app->request->get('keyword');

        $query = User::find()->visible()->search($keyword);

        // Restrict to group
        $group = null;
        $groupId = (int)Yii::$app->request->get('groupId');
        if ($groupId) {
            $group = Group::findOne(['id' => $groupId, 'show_at_directory' => 1]);
            if ($group === null) {
                throw new HttpException(404);
            }
            $query->isGroupMember($group);
        }

        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count()]);

        // Order
        $query->joinWith('profile');
        if (empty($this->module->memberListSortField) || $this->module->memberListSortField === 'lastname' || $this->module->memberListSortField === 'firstname') {
            // Fallback to default value
            $query->addOrderBy('profile.lastname');
        } else {
            $query->addOrderBy($this->module->memberListSortField);
        }

        Event::on(Sidebar::class, Sidebar::EVENT_INIT, function ($event) {
            $event->sender->addWidget(NewMembers::class, [], ['sortOrder' => 10]);
            $event->sender->addWidget(MemberStatistics::class, [], ['sortOrder' => 20]);
        });

        return $this->render('members', [
            'keyword' => $keyword,
            'group' => $group,
            'users' => $query->offset($pagination->offset)->limit($pagination->limit)->all(),
            'pagination' => $pagination
        ]);

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
        $keyword = (string)Yii::$app->request->get('keyword');

        $query = Space::find()->visible()->search($keyword);
        $query->addOrderBy('space.name');

        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count()]);

        Event::on(Sidebar::class, Sidebar::EVENT_INIT, function ($event) {
            $event->sender->addWidget(NewSpaces::class, [], ['sortOrder' => 10]);
            $event->sender->addWidget(SpaceStatistics::class, [], ['sortOrder' => 20]);
        });

        return $this->render('spaces', [
            'keyword' => $keyword,
            'spaces' => $query->offset($pagination->offset)->limit($pagination->limit)->all(),
            'pagination' => $pagination,
        ]);
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

        $groups = Group::getDirectoryGroups();

        Event::on(Sidebar::class, Sidebar::EVENT_INIT, function ($event) {
            $event->sender->addWidget(GroupStatistics::class, [], ['sortOrder' => 10]);
        });

        return $this->render('groups', [
            'groups' => $groups,
        ]);
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
        return $this->render('userPosts', []);
    }

}
