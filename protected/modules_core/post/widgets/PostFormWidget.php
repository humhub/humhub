<?php

/**
 * This widget is used include the post form.
 * It normally should be placed above a steam.
 *
 * @package humhub.modules_core.post.widgets
 * @since 0.5
 */
class PostFormWidget extends HWidget {

    public $target = "";
    public $guid = "";

    public function init() {
        //$assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/../resources', true, 0, defined('YII_DEBUG'));
        //Yii::app()->clientScript->registerScriptFile($assetPrefix . '/postForm.js');
        //Yii::app()->clientScript->registerScriptFile($assetPrefix . '/jquery.iframe-post-form.js');
    }

    public function run() {

        // can create public posts
        $canShare = false;

        if ($this->target == "user") {

            // We cannot write to this user
            $user = User::model()->findByAttributes(array('guid' => $this->guid));
            if ($user != null && !$user->canWrite()) {
                return;
            }
        } elseif ($this->target == "space") {

            // Can this user write to this workspace?
            $space = Space::model()->findByAttributes(array('guid' => $this->guid));
            if ($space != null && !$space->canWrite()) {
                return;
            }

            if ($space->canShare())
                $canShare = true;

            if (!$space->canWrite())
                return;
        } else {
            throw new CHttpException('500', 'Invalid target for postForm!');
        }


        $this->render('postForm', array('target' => $this->target, 'guid' => $this->guid, 'canShare' => $canShare));
    }

}

?>