<?php

/**
 * @package humhub.modules_core.post.controllers
 * @since 0.5
 */
class PostController extends Controller {

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
     * Posts a new entry to the wall
     *
     * @return type
     */
    public function actionPost() {

        $json = array();
        $json['errorMessage'] = "";

        $target = Yii::app()->request->getParam('target', ""); // space, user
        $guid = Yii::app()->request->getParam('guid', ""); // guid of user/space

        // Check mandatory parameters
        if ($target == "" || $guid == "") {
            $json['error'] = true;
            $json['errorMessage'] = Yii::t('PostModule.error', "Missing parameter!");
            echo CJSON::encode($json);
            return Yii::app()->end();
        }

        $message = Yii::app()->request->getParam('message', ""); // content of post
        $message = trim($message);
        $message = CHtml::encode($message);

        // Check we got a message
        if ($message == "") {
            $json['error'] = true;
            $json['errorMessage'] = Yii::t('PostModule.error', "Please enter something!");
            echo CJSON::encode($json);
            return Yii::app()->end();
        }

        // Wall Id to publish to
        $wallId = "";

        $post = new Post();

        // Set some Space Post specific Stuff
        $space = "";
        if ($target == 'space') {
            $space = Space::model()->findByAttributes(array('guid' => $guid));

            if ($space == null)
                throw new CHttpException('404', 'Space not found!');

            // Basic Access checking
            if (!$space->canWrite())
                throw new CHttpException('403', 'Forbidden');

            $wallId = $space->wall_id;
            $post->contentMeta->space_id = $space->id;


            // Set some User Post specific Stuff
        } elseif ($target == 'user') {

            $user = User::model()->findByAttributes(array('guid' => $guid));

            if ($user == null)
                throw new CHttpException('404', 'Space not found!');

            // Checks Basic Access
            if (!$user->canWrite())
                throw new CHttpException('403', 'Forbidden');

            $wallId = $user->wall_id;
            $post->contentMeta->user_id = $user->id;
        } else {
            throw new CHttpException('500', 'Invalid target!');
        }


        $public = 0;
        if ($target == "space" && $space->canShare()) {
            // Public / Private Handling
            $public = (int) Yii::app()->request->getParam('public', 0); // public post?
        } elseif ($target == "user") {
            // User posts are always public, yet
            $public = 1;
        }

        if ($public == 1) {
            $post->contentMeta->visibility = Content::VISIBILITY_PUBLIC;
        } else {
            $post->contentMeta->visibility = Content::VISIBILITY_PRIVATE;
        }


        $post->message = $message;
        $post->save();

        File::attachToContent($post, Yii::app()->request->getParam('fileList', ""));

        // Creates Wall Entry for it
        $wallEntry = $post->contentMeta->addToWall($wallId);

        // Build JSON Out
        $json['error'] = false;
        $json['wallEntryId'] = $wallEntry->id;

        // returns JSON
        echo CJSON::encode($json);
        Yii::app()->end();
    }

}