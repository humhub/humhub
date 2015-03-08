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

    //public $template = "application.widgets.views.leftNavigation";

    /*    public function run()
        {
            $this->render('spaceHeader', array('space' => Yii::app()->getController()->getSpace()));
        }*/

    protected $space;
    protected $isSpaceAdmin = false;

    public function init()
    {
        $this->space = Yii::app()->getController()->getSpace();

        $this->isSpaceAdmin = $this->space->isAdmin();

        // Only include uploading javascripts if user is space admin
        if ($this->isSpaceAdmin) {
            $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/../resources', true, 0, defined('YII_DEBUG'));
            Yii::app()->clientScript->registerScriptFile($assetPrefix . '/spaceHeaderImageUpload.js');

            Yii::app()->clientScript->setJavascriptVariable('profileImageUploaderUrl', Yii::app()->createUrl('//space/admin/imageUpload', array('guid' => $this->space->guid)));
            Yii::app()->clientScript->setJavascriptVariable('profileHeaderUploaderUrl', Yii::app()->createUrl('//space/admin/bannerImageUpload', array('guid' => $this->space->guid)));
        }
    }

    public function run()
    {
        $this->render('spaceHeader', array(
            'space' => $this->space,
            'isSpaceAdmin' => $this->isSpaceAdmin,
        ));
    }


}

?>