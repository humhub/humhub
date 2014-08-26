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


        // check if tour is activated for new users
        if (HSetting::Get('enable', 'tour') == 1) {

            // save in variable, if the tour panel is activated or not
            $hideTourPanel = Yii::app()->user->getModel()->getSetting("hideTourPanel", "tour");

            // if not...
            if ($hideTourPanel == 0) {

                // save current module and controller id's
                $currentModuleId = Yii::app()->controller->module->id;
                $currentControllerId = Yii::app()->controller->id;

                // check current page
                if ($currentModuleId == "dashboard" && $currentControllerId == "dashboard") {

                    // load resource files
                    $this->loadResources();

                    // get the first space in database (should be the welcome space)
                    $space = Space::model()->find();

                    // render tour view
                    $this->render('welcome_interface', array('space' => $space));
                }


                // check current page
                if ($currentModuleId == "space" && $currentControllerId == "space" && isset($_GET['tour'])) {

                    // load resource files
                    $this->loadResources();

                    // render tour view
                    $this->render('spaces', array());
                }


                // check current page
                if ($currentModuleId == "user" && $currentControllerId == "profile" && isset($_GET['tour'])) {

                    // load resource files
                    $this->loadResources();

                    // render tour view
                    $this->render('profile', array());
                }


                // check current page
                if ($currentModuleId == "admin" && $currentControllerId == "module" && isset($_GET['tour'])) {

                    // load resource files
                    $this->loadResources();

                    // render tour view
                    $this->render('administration', array());
                }


            }

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
