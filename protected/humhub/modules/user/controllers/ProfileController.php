<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use Yii;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\UserListBox;

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
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'guestAllowedActions' => ['index', 'stream', 'about']
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
                'class' => \humhub\modules\content\components\actions\ContentContainerStream::className(),
                'mode' => \humhub\modules\content\components\actions\ContentContainerStream::MODE_NORMAL,
                'contentContainer' => $this->contentContainer
            ),
        );
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
        return $this->render('home', ['user' => $this->contentContainer]);
    }

    public function actionAbout()
    {
        if (!$this->contentContainer->permissionManager->can(new \humhub\modules\user\permissions\ViewAboutPage())) {
            throw new \yii\web\HttpException(403, 'Forbidden');
        }

        return $this->render('about', ['user' => $this->contentContainer]);
    }

    public function actionFollow()
    {
        $this->forcePostRequest();
        $this->getUser()->follow();

        if (Yii::$app->request->isAjax) {
            return;
        }

        return $this->redirect($this->getUser()->getUrl());
    }

    public function actionUnfollow()
    {
        $this->forcePostRequest();
        $this->getUser()->unfollow();

        if (Yii::$app->request->isAjax) {
            return;
        }

        return $this->redirect($this->getUser()->getUrl());
    }

    public function actionFollowerList()
    {
        $query = User::find();
        $query->leftJoin('user_follow', 'user.id=user_follow.user_id and object_model=:userClass and user_follow.object_id=:userId', [':userClass' => User::className(), ':userId' => $this->getUser()->id]);
        $query->orderBy(['user_follow.id' => SORT_DESC]);
        $query->andWhere(['IS NOT', 'user_follow.id', new \yii\db\Expression('NULL')]);
        $query->active();

        $title = Yii::t('UserModule.widgets_views_userFollower', '<strong>User</strong> followers');
        return $this->renderAjaxContent(UserListBox::widget(['query' => $query, 'title' => $title]));
    }

    public function actionFollowedUsersList()
    {
        $query = User::find();
        $query->leftJoin('user_follow', 'user.id=user_follow.object_id and object_model=:userClass and user_follow.user_id=:userId', [':userClass' => User::className(), ':userId' => $this->getUser()->id]);
        $query->orderBy(['user_follow.id' => SORT_DESC]);
        $query->andWhere(['IS NOT', 'user_follow.id', new \yii\db\Expression('NULL')]);
        $query->active();

        $title = Yii::t('UserModule.widgets_views_userFollower', '<strong>Following</strong> user');
        return $this->renderAjaxContent(UserListBox::widget(['query' => $query, 'title' => $title]));
    }

    public function actionSpaceMembershipList()
    {
        $query = \humhub\modules\space\models\Membership::getUserSpaceQuery($this->getUser());

        if (!$this->getUser()->isCurrentUser()) {
            $query->andWhere(['!=', 'space.visibility', \humhub\modules\space\models\Space::VISIBILITY_NONE]);
        }

        $title = Yii::t('UserModule.widgets_views_userSpaces', '<strong>Member</strong> in these spaces');
        return $this->renderAjaxContent(\humhub\modules\space\widgets\ListBox::widget(['query' => $query, 'title' => $title]));
    }

}

?>
