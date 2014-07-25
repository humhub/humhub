<?php

/**
 * TourController
 *
 * @author andystrobel
 * @package humhub.modules_core.tour.controllers
 * @since 0.5
 */
class TourController extends Controller {

    public function actionSeen() {

        // set tour status to seen for current user
        Yii::app()->user->getModel()->setSetting("seen", "true", "tour");
    }

}