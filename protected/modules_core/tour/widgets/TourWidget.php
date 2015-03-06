<?php

/**
 * Will show the introduction tour
 *
 * @package humhub.modules_core.tour.widgets
 * @since 0.5
 * @author andystrobel
 */
class TourWidget extends HWidget
{

    /**
     * Executes the widgets
     */
    public function run()
    {
        if (Yii::app()->user->isGuest)
            return;

        // Active tour flag not set
        if (!isset($_GET['tour'])) {
            return;
        }

        // Tour only possible when we are in a module
        if (Yii::app()->controller->module === null) {
            return;
        }

        // Check if tour is activated by admin and users
        if (HSetting::Get('enable', 'tour') == 0 || Yii::app()->user->getModel()->getSetting("hideTourPanel", "tour") == 1) {
            return;
        }

        $this->loadResources();

        // save current module and controller id's
        $currentModuleId = Yii::app()->controller->module->id;
        $currentControllerId = Yii::app()->controller->id;

        if ($currentModuleId == "dashboard" && $currentControllerId == "dashboard") {
            $this->render('guide_interface');
        } elseif ($currentModuleId == "space" && $currentControllerId == "space") {
            $this->render('guide_spaces', array());
        } elseif ($currentModuleId == "user" && $currentControllerId == "profile") {
            $this->render('guide_profile', array());
        } elseif ($currentModuleId == "admin" && $currentControllerId == "module") {
            $this->render('guide_administration', array());
        }
    }

    /**
     * load needed resources files
     */
    public function loadResources()
    {
        $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/../resources', true, 0, defined('YII_DEBUG'));
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/bootstrap-tour.min.js');
        Yii::app()->clientScript->registerCssFile($assetPrefix . '/bootstrap-tour.min.css');
    }

}

?>
