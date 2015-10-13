<?php

/**
 * Created by PhpStorm.
 * User: Struppi
 * Date: 17.12.13
 * Time: 12:49
 */
class SpaceChooserWidget extends HWidget
{

    public function init()
    {
        // publish resource files
        $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/../resources', true, 0, defined('YII_DEBUG'));
        Yii::app()->clientScript->setJavascriptVariable('scSpaceListUrl', $this->createUrl('//space/list', array('ajax' => 1)));
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/spacechooser.js');
    }

    /**
     * Displays / Run the Widgets
     */
    public function run()
    {
        if (Yii::app()->user->isGuest)
            return;

        $this->render('spaceChooser', array());
    }

}

?>