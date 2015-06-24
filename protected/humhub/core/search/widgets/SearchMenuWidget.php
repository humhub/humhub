<?php

class SearchMenuWidget extends HWidget
{

    public function init()
    {
        $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/../assets', true, 0, defined('YII_DEBUG'));
        //Yii::app()->clientScript->registerScriptFile($assetPrefix . '/searchmenu.js');
        //Yii::app()->clientScript->setJavaScriptVariable('searchAjaxUrl', $this->createUrl('//search/search/index', array('mode' => 'quick', 'keyword' => '-searchKeyword-')));
    }

    public function run()
    {
        $this->render('searchMenu', array());
    }

}
