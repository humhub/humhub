<?php

namespace humhub\modules\tour\widgets;

use humhub\modules\tour\assets\TourAsset;
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

        TourAsset::register($this->view);

        // save current module and controller id's
        $currentModuleId = Yii::$app->controller->module->id;
        $currentControllerId = Yii::$app->controller->id;

        if ($currentModuleId == "dashboard" && $currentControllerId == "dashboard") {
            return $this->render('guide_interface');
        } elseif ($currentModuleId == "space" && $currentControllerId == "space") {
            return $this->render('guide_spaces', []);
        } elseif ($currentModuleId == "user" && $currentControllerId == "profile") {
            return $this->render('guide_profile', []);
        } elseif ($currentModuleId == "marketplace" && $currentControllerId == "browse") {
            return $this->render('guide_administration', []);
        }
    }

    /**
     * @deprecated since 1.3.13
     */
    public function loadResources(\yii\web\View $view)
    {
        // Dummy for old template version
    }

}

?>
