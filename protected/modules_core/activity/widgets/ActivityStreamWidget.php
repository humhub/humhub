<?php

/**
 * ActivityStreamWidget shows an stream/wall of activities inside a sidebar.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.modules_core.activity
 * @since 0.5
 */
class ActivityStreamWidget extends HWidget
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
     * Inits the activity stream widget
     */
    public function init()
    {
        if ($this->streamAction == "") {
            throw new CHttpException(500, 'You need to set the streamAction attribute to use this widget!');
        }

        $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/../assets', true, 0, defined('YII_DEBUG'));
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/activies.js');

        Yii::app()->clientScript->setJavascriptVariable('activityStreamUrl', $this->getStreamUrl());
        Yii::app()->clientScript->setJavascriptVariable('activityPermaLinkUrl', Yii::app()->createUrl('//wall/perma/wallEntry'));
    }

    /**
     * Runs the activity widget
     */
    public function run()
    {
        $this->render('activityStream', array());
    }

    protected function getStreamUrl()
    {
        $params = array(
            'limit' => '10',
            'from' => '-from-',
            'mode' => BaseStreamAction::MODE_ACTIVITY
        );

        if ($this->contentContainer) {
            return $this->contentContainer->createUrl($this->streamAction, $params);
        }

        return Yii::app()->createUrl($this->streamAction, $params);
    }

}

?>