<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers;

use Yii;
use yii\web\HttpException;
use yii\db\Expression;
use humhub\modules\content\components\ContentContainerController;
use humhub\components\behaviors\AccessControl;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\UserListBox;
use humhub\modules\stream\actions\ContentContainerStream;
use humhub\modules\space\widgets\Menu;
use humhub\modules\post\permissions\CreatePost;

/**
 * SpaceController is the main controller for spaces.
 *
 * It show the space itself and handles all related tasks like following or
 * memberships.
 *
 * @author Luke
 * @package humhub.modules_core.space.controllers
 * @since 0.5
 */
class SpaceController extends ContentContainerController
{

    /**
     * @inheritdoc
     */
    public $hideSidebar = false;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::className(),
                'guestAllowedActions' => ['index', 'stream'],
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return array(
            'stream' => array(
                'class' => ContentContainerStream::className(),
                'contentContainer' => $this->contentContainer
            ),
        );
    }

    /**
     * Generic Start Action for Profile
     */
    public function actionIndex()
    {
        $space = $this->getSpace();

        if (Yii::$app->request->get('tour') || Yii::$app->request->get('contentId')) {
            return $this->actionHome();
        }

        if (!$space->isMember()) {
            $defaultPageUrl = Menu::getGuestsDefaultPageUrl($space);
            if ($defaultPageUrl != null) {
                return $this->redirect($defaultPageUrl);
            }
        }

        $defaultPageUrl = Menu::getDefaultPageUrl($space);
        if ($defaultPageUrl != null) {
            return $this->redirect($defaultPageUrl);
        }

        return $this->actionHome();
    }

    /**
     * Default space homepage
     * 
     * @return type
     */
    public function actionHome()
    {
        $space = $this->contentContainer;
        $canCreatePosts = $space->permissionManager->can(new CreatePost());
        $isMember = $space->isMember();

        return $this->render('home', [
                    'space' => $space,
                    'canCreatePosts' => $canCreatePosts,
                    'isMember' => $isMember
        ]);
    }

    /**
     * Follows a Space
     */
    public function actionFollow()
    {
        if(Yii::$app->getModule('space')->disableFollow) {
            throw new HttpException(403, Yii::t('ContentModule.controllers_ContentController', 'This action is disabled!'));
        }
        
        $this->forcePostRequest();
        $space = $this->getSpace();

        $success = false;

        if (!$space->isMember()) {
            // follow without notifications by default
            $success = $space->follow(null, false);
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            return ['success' => $success];
        }

        return $this->redirect($space->getUrl());
    }

    /**
     * Unfollows a Space
     */
    public function actionUnfollow()
    {
        $this->forcePostRequest();
        $space = $this->getSpace();

        $success = $space->unfollow();

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            return ['success' => $success];
        }

        return $this->redirect($space->getUrl());
    }

    /**
     * Modal to  list followers of a space
     */
    public function actionFollowerList()
    {
        $query = User::find();
        $query->leftJoin('user_follow', 'user.id=user_follow.user_id AND object_model=:userClass AND user_follow.object_id=:spaceId', [':userClass' => Space::className(), ':spaceId' => $this->getSpace()->id]);
        $query->orderBy(['user_follow.id' => SORT_DESC]);
        $query->andWhere(['IS NOT', 'user_follow.id', new Expression('NULL')]);
        $query->active();

        $title = Yii::t('SpaceModule.base', '<strong>Space</strong> followers');

        return $this->renderAjaxContent(UserListBox::widget(['query' => $query, 'title' => $title]));
    }
   
}
