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
        $this->renderPartial('seen');
    }

}