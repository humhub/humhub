<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers;

use Yii;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\UserListBox;


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
class SpaceController extends \humhub\modules\content\components\ContentContainerController
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'guestAllowedActions' => ['index', 'stream']
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

        if (!$space->isMember()) {
            $defaultPageUrl = \humhub\modules\space\widgets\Menu::getGuestsDefaultPageUrl($space);
            if ($defaultPageUrl != null) {
                return $this->redirect($defaultPageUrl);
            }
        }

        $defaultPageUrl = \humhub\modules\space\widgets\Menu::getDefaultPageUrl($space);
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
        $canCreatePosts = $space->permissionManager->can(new \humhub\modules\post\permissions\CreatePost());
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
        $this->forcePostRequest();
        $space = $this->getSpace();
        if (!$space->isMember()) {
            $space->follow();
        }

        if (Yii::$app->request->isAjax) {
            return;
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
        $space->unfollow();

        if (Yii::$app->request->isAjax) {
            return;
        }

        return $this->redirect($space->getUrl());
    }

    /**
     * Modal to  list followers of a space
     */
    public function actionFollowerList()
    {
        $query = User::find();
        $query->leftJoin('user_follow', 'user.id=user_follow.user_id and object_model=:userClass and user_follow.object_id=:spaceId', [':userClass' => Space::className(), ':spaceId' => $this->getSpace()->id]);
        $query->orderBy(['user_follow.id' => SORT_DESC]);
        $query->andWhere(['IS NOT', 'user_follow.id', new \yii\db\Expression('NULL')]);
        $query->active();

        $title = Yii::t('SpaceModule.base', '<strong>Space</strong> followers');
        return $this->renderAjaxContent(UserListBox::widget(['query' => $query, 'title' => $title]));
    }

}

?>
