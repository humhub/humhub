<?php

/**
 * Wall Stream Widget creates a wall widget.
 * 
 * *** DEPRECATED since 0.11 use StreamWidget instead! ***
 * 
 * @package humhub.modules_core.wall.widgets
 * @since 0.5
 * @deprecated since version 0.11
 */
class WallStreamWidget extends HWidget
{

    /**
     * Type of Stream (Wall::TYPE_*)
     *
     * Can be null if content container is set.
     *
     * @var string
     */
    public $type;

    /**
     * ContentContainer (e.g. User, Space) which this space belongs to
     *
     * @var HActiveRecordContentContainer
     */
    public $contentContainer;

    /**
     * Path to Stream Action to use
     *
     * @var string
     */
    public $streamAction = "//wall/wall/stream";

    /**
     * Number of stream content loaded by request.
     * 
     * Should be at least 4, because its possible to stick at maximum 3 object
     * Otherwise sticky system may break the wall
     * 
     * @var int
     */
    public $wallObjectStreamLimit = 4;

    /**
     * Inits the Wall Stream Widget
     */
    public function init()
    {

        if ($this->contentContainer != null) {
            $this->type = get_class($this->contentContainer);
        }

        Yii::app()->clientScript->registerScriptFile(
                Yii::app()->assetManager->publish(
                        Yii::getPathOfAlias('application.modules_core.wall.assets.deprecated') . '/si_streaming.js'
                ), CClientScript::POS_BEGIN
        );

        Yii::app()->clientScript->registerScriptFile(
                Yii::app()->assetManager->publish(
                        Yii::getPathOfAlias('application.modules_core.wall.assets') . '/wall.js'
                ), CClientScript::POS_BEGIN
        );
    }

    /**
     * Creates the Wall Widget
     */
    public function run()
    {

        // Save Wall Type
        Wall::$currentType = $this->type;

        $guid = null;
        if ($this->contentContainer != "") {
            $guid = $this->contentContainer->guid;
        }

        // Set some Urls for this wall
        $reloadUrl = Yii::app()->createUrl($this->streamAction, array('type' => $this->type, 'guid' => $guid, 'limit' => $this->wallObjectStreamLimit, 'from' => 'lastEntryId', 'filters' => 'filter_placeholder', 'sort' => 'sort_placeholder'));
        $startUrl = Yii::app()->createUrl($this->streamAction, array('type' => $this->type, 'guid' => $guid, 'limit' => $this->wallObjectStreamLimit, 'filters' => 'filter_placeholder', 'sort' => 'sort_placeholder'));
        $singleEntryUrl = Yii::app()->createUrl($this->streamAction, array('type' => $this->type, 'guid' => $guid, 'limit' => 1, 'from' => 'fromEntryId'));

        $view = 'stream_deprecated';

        /**
         * For backward compatiblity use modules 'stream' view
         */
        if (get_class($this) != 'WallStreamWidget') {
            $view = 'stream';
        }
        
        // Render It
        $this->render($view, array(
            'type' => $this->type,
            'reloadUrl' => $reloadUrl,
            'startUrl' => $startUrl,
            'singleEntryUrl' => $singleEntryUrl,
        ));
    }

}

?>