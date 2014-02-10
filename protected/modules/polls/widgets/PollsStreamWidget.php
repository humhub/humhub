<?php

/**
 * PollsStreamWidget is used show a stream of poll objects only.
 *
 * @package humhub.modules.polls.widgets
 * @since 0.5
 * @author Luke
 */
class PollsStreamWidget extends HWidget {

    public $type = "Space";
    public $guid;    // guid of user or workspace

    /**
     * Registeres the Stream Widget
     */

    public function init() {

        Yii::app()->clientScript->registerScriptFile(
                Yii::app()->assetManager->publish(
                        Yii::getPathOfAlias('application.modules_core.wall.resources') . '/si_streaming.js'
                ), CClientScript::POS_BEGIN
        );

        Yii::app()->clientScript->registerScriptFile(
                Yii::app()->assetManager->publish(
                        Yii::getPathOfAlias('application.modules_core.wall.resources') . '/jquery.timeago.js'
                ), CClientScript::POS_BEGIN
        );

        Yii::app()->clientScript->registerScriptFile(
                Yii::app()->assetManager->publish(
                        Yii::getPathOfAlias('application.modules_core.wall.resources') . '/wall.js'
                ), CClientScript::POS_BEGIN
        );
    }

    /**
     * Creates the Wall Widget
     */
    public function run() {

        // Save Wall Type
        Wall::$currentType = $this->type;

        $reloadUrl = Yii::app()->createUrl('polls/poll/stream', array('type' => $this->type, 'guid' => $this->guid, 'limit' => 4, 'from' => 'lastEntryId', 'filters' => 'filter_placeholder'));
        $startUrl = Yii::app()->createUrl('polls/poll/stream', array('type' => $this->type, 'guid' => $this->guid, 'limit' => 4, 'filters' => 'filter_placeholder'));
        $singleEntryUrl = Yii::app()->createUrl('polls/poll/stream', array('type' => $this->type, 'guid' => $this->guid, 'limit' => 1, 'from' => 'fromEntryId'));

        $this->render('stream', array(
            'type' => $this->type,
            'reloadUrl' => $reloadUrl,
            'startUrl' => $startUrl,
            'singleEntryUrl' => $singleEntryUrl,
        ));
    }

}

?>