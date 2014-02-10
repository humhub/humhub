<?php

/**
 * ProfileController is responsible for all user profiles.
 * Also the following functions are implemented here.
 *
 * @author Luke
 * @package humhub.modules_core.user.controllers
 * @since 0.5
 */
class ProfileController extends Controller {

    public $subLayout = "_layout";

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'users' => array('@'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Add mix-ins to this model
     *
     * @return type
     */
    public function behaviors() {
        return array(
            'ProfileControllerBehavior' => array(
                'class' => 'application.modules_core.user.ProfileControllerBehavior',
            ),
        );
    }

    /**
     *
     */
    public function actionIndex() {
        $this->render('index');
    }

    /**
     *
     */
    public function actionAbout() {
        $this->render('about',array('user'=>$this->getUser()));
    }

    /**
     * Unfollows a User
     *
     */
    public function actionFollow() {

        if (Yii::app()->user->isGuest)
            Yii::app()->user->loginRequired();

        $haunted = $this->getUser();

        // Self Following?
        if ($haunted->id == Yii::app()->user->id)
            throw new CHttpException(500, 'Not supported!');

        // Really not folllowed yet?
        if (!$haunted->isFollowedBy(Yii::app()->user->id)) {

            // Create Follower Object
            $follow = new UserFollow;
            $follow->user_followed_id = $haunted->id;
            $follow->user_follower_id = Yii::app()->user->id;
            $follow->save();
        }


        $this->redirect($this->createUrl('profile/', array('guid' => $haunted->guid)));
    }

    /**
     * Unfollows a User
     */
    public function actionUnfollow() {

        if (Yii::app()->user->isGuest)
            Yii::app()->user->loginRequired();

        $haunted = $this->getUser();

        if ($haunted->isFollowedBy(Yii::app()->user->id)) {

            $follow = UserFollow::model()->findByAttributes(array(
                'user_follower_id' => Yii::app()->user->id,
                'user_followed_id' => $haunted->id,
            ));
            $follow->delete();
        }

        $this->redirect($this->createUrl('profile/', array('guid' => $haunted->guid)));
    }

}

?>
