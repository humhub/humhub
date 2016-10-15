<?php

namespace humhub\modules\tour\widgets;

use Yii;


/**
 * Will show the introduction tour
 *
 * @package humhub.modules_core.tour.widgets
 * @since 0.5
 * @author andystrobel
 */
class Tour extends \humhub\components\Widget
{

    /**
     * Executes the widgets
     */
    public function run()
    {

        if (Yii::$app->user->isGuest)
            return;

        // Active tour flag not set
        if (!isset($_GET['tour'])) {
            return;
        }


        // Tour only possible when we are in a module
        if (Yii::$app->controller->module === null) {
            return;
        }

        // Check if tour is activated by admin and users
        $settings = Yii::$app->getModule('tour')->settings;
        if ($settings->get('enable') == 0 && $settings->user()->get("hideTourPanel") == 1) {
            return;
        }

        // save current module and controller id's
        $currentModuleId = Yii::$app->controller->module->id;
        $currentControllerId = Yii::$app->controller->id;

        if ($currentModuleId == "dashboard" && $currentControllerId == "dashboard") {
            return $this->render('guide_interface');
        } elseif ($currentModuleId == "space" && $currentControllerId == "space") {
            return $this->render('guide_spaces', array());
        } elseif ($currentModuleId == "user" && $currentControllerId == "profile") {
            return $this->render('guide_profile', array());
        } elseif ($currentModuleId == "admin" && $currentControllerId == "module") {
            return $this->render('guide_administration', array());
        }
    }

    /**
     * load needed resources files
     */
    public function loadResources(\yii\web\View $view)
    {
        $view->registerJsFile('@web/resources/tour/bootstrap-tour.min.js');
        $view->registerCssFile('@web/resources/tour/bootstrap-tour.min.css');
    }

}

?>
