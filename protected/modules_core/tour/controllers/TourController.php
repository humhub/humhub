<?php

/**
 * TourController
 *
 * @author andystrobel
 * @package humhub.modules_core.tour.controllers
 * @since 0.5
 */
class TourController extends Controller {

    /**
     * Update user settings for completed tours
     */
    public function actionTourCompleted() {

        // get section parameter from completed tour
        $section = Yii::app()->request->getParam('section');

        // set tour status to seen for current user
        Yii::app()->user->getModel()->setSetting($section, 1, "tour");
    }

    /*
     * Update user settings for hiding tour panel on dashboard
     */
    public function actionHidePanel() {

        // set tour status to seen for current user
        Yii::app()->user->getModel()->setSetting('hideTourPanel', 1, "tour");

    }

}