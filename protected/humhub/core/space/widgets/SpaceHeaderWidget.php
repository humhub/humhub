<?php

/**
 * This widget will added to the sidebar and show infos about the current selected space
 *
 * @author Andreas Strobel
 * @package humhub.modules_core.space.widgets
 * @since 0.5
 */
class SpaceHeaderWidget extends HWidget
{

    public $space;

    public function init()
    {
        // Only include uploading javascripts if user is space admin
        if ($this->space->isAdmin()) {
            $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/../resources', true, 0, defined('YII_DEBUG'));
            Yii::app()->clientScript->registerScriptFile($assetPrefix . '/spaceHeaderImageUpload.js');

            Yii::app()->clientScript->setJavascriptVariable('profileImageUploaderUrl', $this->space->createUrl('//space/admin/imageUpload'));
            Yii::app()->clientScript->setJavascriptVariable('profileHeaderUploaderUrl', $this->space->createUrl('//space/admin/bannerImageUpload'));
        }
    }

    public function run()
    {
        $this->render('spaceHeader', array(
            'space' => $this->space,
        ));
    }

}

?>