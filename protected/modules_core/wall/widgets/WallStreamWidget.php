<?php

/**
 * Wall Stream Widget creates a wall widget.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.5
 */
class WallStreamWidget extends HWidget {

    /**
     * @var string type of the wall (dashboard, space, user)
     */
    public $type;

    /**
     * @var string in case of space or user wall, the space/user guid
     */
    public $guid;

    /**
     * @var boolean Read Only (disable all wall methods, like comment, like, delete)
     */
    public $readonly = false;

    /**
     * Inits the Wall Stream Widget
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

        // Should be at least 4, because its possible to stick at maximum 3 object
        // Otherwise sticky system may break the wall
        $wallObjectStreamLimit = 4;

        // Save Wall Type
        Wall::$currentType = $this->type;

        // Set some Urls for this wall
        $reloadUrl = Yii::app()->createUrl('//wall/wall/stream', array('type' => $this->type, 'guid' => $this->guid, 'limit' => $wallObjectStreamLimit, 'from' => 'lastEntryId', 'filters' => 'filter_placeholder', 'sort' => 'sort_placeholder'));
        $startUrl = Yii::app()->createUrl('//wall/wall/stream', array('type' => $this->type, 'guid' => $this->guid, 'limit' => $wallObjectStreamLimit, 'filters' => 'filter_placeholder', 'sort' => 'sort_placeholder'));
        $singleEntryUrl = Yii::app()->createUrl('//wall/wall/stream', array('type' => $this->type, 'guid' => $this->guid, 'limit' => 1, 'from' => 'fromEntryId'));

        // Render It
        $this->render('wall', array(
            'type' => $this->type,
            'reloadUrl' => $reloadUrl,
            'startUrl' => $startUrl,
            'singleEntryUrl' => $singleEntryUrl,
            'readonly' => $this->readonly,
        ));
    }

}

?>