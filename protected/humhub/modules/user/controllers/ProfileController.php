<?php

namespace humhub\modules\user\controllers;

use Yii;
use humhub\modules\content\components\ContentContainerController;

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

    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'guestAllowedActions' => ['index', 'stream', 'about']
            ]
        ];
    }

    public function actions()
    {
        return array(
            'stream' => array(
                'class' => \humhub\modules\content\components\actions\ContentContainerStream::className(),
                'mode' => \humhub\modules\content\components\actions\ContentContainerStream::MODE_NORMAL,
                'contentContainer' => $this->getUser()
            ),
        );
    }

    /**
     *
     */
    public function actionIndex()
    {
        return $this->render('index', ['user' => $this->contentContainer]);
    }

    /**
     *
     */
    public function actionAbout()
    {
        return $this->render('about', ['user' => $this->contentContainer]);
    }

    /**
     * Unfollows a User
     *
     */
    public function actionFollow()
    {
        $this->forcePostRequest();
        $this->getUser()->follow();
        return $this->redirect($this->getUser()->getUrl());
    }

    /**
     * Unfollows a User
     */
    public function actionUnfollow()
    {
        $this->forcePostRequest();
        $this->getUser()->unfollow();
        return $this->redirect($this->getUser()->getUrl());
    }

}

?>
