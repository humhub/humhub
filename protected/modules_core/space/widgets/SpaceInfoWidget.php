<?php

/**
 * This widget will added to the sidebar and show infos about the current selected space
 *
 * @author Andreas Strobel
 * @package humhub.modules_core.space.widgets
 * @since 0.5
 */
class SpaceInfoWidget extends HWidget {

    //public $template = "application.widgets.views.leftNavigation";

    public function run() {

        $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/../resources', true, 0, defined('YII_DEBUG'));
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/jquery.ui.widget.js');
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/jquery.iframe-transport.js');
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/jquery.fileupload.js');

        $this->render('spaceInfo', array('space' => Yii::app()->getController()->getSpace()));
    }

}

?>