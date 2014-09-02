<?php

/**
 * TourController
 *
 * @author andystrobel
 * @package humhub.modules_core.tour.controllers
 * @since 0.5
 */
class TourController extends Controller
{

    /**
     * Update user settings for completed tours
     */
    public function actionTourCompleted()
    {

        // get section parameter from completed tour
        $section = Yii::app()->request->getParam('section');

        if (!in_array($section, array('interface', 'administration', 'profile', 'spaces')))
            return;

        // set tour status to seen for current user
        Yii::app()->user->getModel()->setSetting($section, 1, "tour");
    }

    /*
     * Update user settings for hiding tour panel on dashboard
     */

    public function actionHidePanel()
    {
        // set tour status to seen for current user
        Yii::app()->user->getModel()->setSetting('hideTourPanel', 1, "tour");
    }

    /**
     * This is a special case, because we need to find a space to start the tour
     */
    public function actionStartSpaceTour()
    {

        $space = null;

        // Loop over all spaces where the user is member
        foreach (SpaceMembership::GetUserSpaces() as $space) {
            if ($space->isAdmin()) {
                // If user is admin on this space, it´s the perfect match
                break;
            }
        }

        if ($space === null) {
            // If user is not member of any space, try to find a public space
            // to run tour in
            $space = Space::model()->find('visibility!=' . Space::VISIBILITY_NONE);
        }

        if ($space === null) {
            throw new CHttpException(404, 'Could not find any public space to run tour!');
        }

        $this->redirect($space->getUrl(array('tour' => true)));
    }

}
