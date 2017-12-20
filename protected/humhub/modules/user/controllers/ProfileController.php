<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use Yii;
use yii\web\HttpException;
use yii\db\Expression;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\stream\actions\ContentContainerStream;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\UserListBox;
use humhub\modules\space\widgets\ListBox;
use humhub\components\behaviors\AccessControl;
use humhub\modules\user\permissions\ViewAboutPage;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;

/**
 * ProfileController is responsible for all user profiles.
 * Also the following functions are implemented here.
 *
 * @author Luke
 * @package humhub.modules_core.user.controllers
 * @since 0.5
 */
class ProfileController extends ContentContainerController
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::className(),
                'guestAllowedActions' => ['index', 'stream', 'about']
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
                'class' => ContentContainerStream::className(),
                'mode' => ContentContainerStream::MODE_NORMAL,
                'contentContainer' => $this->contentContainer
            ],
        ];
    }

    /**
     * User profile home
     * 
     * @todo Allow change of default action
     * @return string the response
     */
    public function actionIndex()
    {
        if ($this->module->profileDefaultRoute !== null) {
            return $this->redirect($this->getUser()->createUrl($this->module->profileDefaultRoute));
        }

        return $this->actionHome();
    }

    public function actionHome()
    {
        $this->hideSidebar = false;
        return $this->render('home', ['user' => $this->contentContainer]);
    }

    public function actionAbout()
    {
        if (!$this->contentContainer->permissionManager->can(new ViewAboutPage())) {
            throw new HttpException(403, 'Forbidden');
        }

        $this->hideSidebar = false;
        return $this->render('about', ['user' => $this->contentContainer]);
    }

    public function actionFollow()
    {
        if(Yii::$app->getModule('user')->disableFollow) {
            throw new HttpException(403, Yii::t('ContentModule.controllers_ContentController', 'This action is disabled!'));
        }
        
        $this->forcePostRequest();
        $this->getUser()->follow(Yii::$app->user->getIdentity(), false);

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            return ['success' => true];
        }

        return $this->redirect($this->getUser()->getUrl());
    }

    public function actionUnfollow()
    {
        $this->forcePostRequest();
        $this->getUser()->unfollow();

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            return ['success' => true];
        }

        return $this->redirect($this->getUser()->getUrl());
    }

    public function actionFollowerList()
    {
        $query = User::find();
        $query->leftJoin('user_follow', 'user.id=user_follow.user_id AND object_model=:userClass AND user_follow.object_id=:userId', [':userClass' => User::className(), ':userId' => $this->getUser()->id]);
        $query->orderBy(['user_follow.id' => SORT_DESC]);
        $query->andWhere(['IS NOT', 'user_follow.id', new Expression('NULL')]);
        $query->active();

        $title = Yii::t('UserModule.widgets_views_userFollower', '<strong>User</strong> followers');

        return $this->renderAjaxContent(UserListBox::widget(['query' => $query, 'title' => $title]));
    }

    public function actionFollowedUsersList()
    {
        $query = User::find();
        $query->leftJoin('user_follow', 'user.id=user_follow.object_id AND object_model=:userClass AND user_follow.user_id=:userId', [':userClass' => User::className(), ':userId' => $this->getUser()->id]);
        $query->orderBy(['user_follow.id' => SORT_DESC]);
        $query->andWhere(['IS NOT', 'user_follow.id', new Expression('NULL')]);
        $query->active();

        $title = Yii::t('UserModule.widgets_views_userFollower', '<strong>Following</strong> user');
        return $this->renderAjaxContent(UserListBox::widget(['query' => $query, 'title' => $title]));
    }

    public function actionSpaceMembershipList()
    {
        $query = Membership::getUserSpaceQuery($this->getUser());

        if (!$this->getUser()->isCurrentUser()) {
            $query->andWhere(['!=', 'space.visibility', Space::VISIBILITY_NONE]);
        }

        $title = Yii::t('UserModule.widgets_views_userSpaces', '<strong>Member</strong> in these spaces');
        return $this->renderAjaxContent(ListBox::widget(['query' => $query, 'title' => $title]));
    }

}
