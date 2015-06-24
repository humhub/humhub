<?php

/**
 * Wall Stream Widget creates a wall widget.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.11
 */
class StreamWidget extends HWidget
{

    /**
     * Optional content container if this stream belongs to one
     *
     * @var HActiveRecordContentContainer
     */
    public $contentContainer;

    /**
     * Path to Stream Action to use
     *
     * @var string
     */
    public $streamAction = "";

    /**
     * Show default wall filters
     * 
     * @var boolean
     */
    public $showFilters = true;

    /**
     * Message when stream is empty and filters are active
     * 
     * @var string
     */
    public $messageStreamEmptyWithFilters = "";

    /**
     * CSS Class(es) for empty stream error with enabled filters
     * 
     * @var string
     */
    public $messageStreamEmptyWithFiltersCss = "";

    /**
     * Message when stream is empty
     * 
     * @var string
     */
    public $messageStreamEmpty = "";

    /**
     * CSS Class(es) for message when stream is empty
     * 
     * @var string
     */
    public $messageStreamEmptyCss = "";

    /**
     * Inits the Wall Stream Widget
     */
    public function init()
    {

        if ($this->streamAction == "") {
            throw new CHttpException(500, 'You need to set the streamAction attribute to use this widget!');
        }

        Yii::app()->clientScript->registerScriptFile(
                Yii::app()->assetManager->publish(
                        Yii::getPathOfAlias('application.modules_core.wall.assets') . '/stream.js'
                ), CClientScript::POS_BEGIN
        );
        Yii::app()->clientScript->registerScriptFile(
                Yii::app()->assetManager->publish(
                        Yii::getPathOfAlias('application.modules_core.wall.assets') . '/wall.js'
                ), CClientScript::POS_BEGIN
        );

        Yii::app()->clientScript->setJavascriptVariable('streamUrl', $this->getStreamUrl());

        $jsLoadWall = "s = new Stream('#wallStream');\n";
        //$jsLoadWall -= "s.markAsReadOnly();\n";
        $wallEntryId = (int) Yii::app()->request->getParam('wallEntryId');
        if ($wallEntryId != "") {
            $jsLoadWall .= "s.showItem(" . $wallEntryId . ");\n";
        } else {
            $jsLoadWall .= "s.showStream();\n";
        }
        $jsLoadWall .= "currentStream = s;\n";
        $jsLoadWall .= "mainStream = s;\n";
        $jsLoadWall .= "$('#btn-load-more').click(function() { currentStream.loadMore(); })\n";
        Yii::app()->clientScript->registerScript('streamLoad', $jsLoadWall);


        /**
         * Setup default messages
         */
        if ($this->messageStreamEmpty == "") {
            $this->messageStreamEmpty = Yii::t('WallModule.widgets_StreamWidget', 'Nothing here yet!');
        }
        if ($this->messageStreamEmptyWithFilters == "") {
            $this->messageStreamEmptyWithFilters = Yii::t('WallModule.widgets_StreamWidget', 'No matches with your selected filters!');
        }
    }

    /**
     * Creates url to stream BaseStreamAction including placeholders
     * which are replaced and handled by javascript.
     * 
     * If a contentContainer is specified it will be used to create the url.
     * 
     * @return string
     */
    protected function getStreamUrl()
    {
        $params = array(
            'limit' => '-limit-',
            'filters' => '-filter-',
            'sort' => '-sort-',
            'from' => '-from-',
            'mode' => BaseStreamAction::MODE_NORMAL
        );

        if ($this->contentContainer) {
            return $this->contentContainer->createUrl($this->streamAction, $params);
        }

        return Yii::app()->createUrl($this->streamAction, $params);
    }

    /**
     * Creates the Wall Widget
     */
    public function run()
    {
        $this->render('stream', array());
    }

}

?>