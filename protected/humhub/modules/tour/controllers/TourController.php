<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\tour\controllers;

use humhub\modules\space\models\Membership;
use Yii;
use yii\web\HttpException;
use humhub\modules\space\models\Space;


/**
 * TourController
 *
 * @author andystrobel
 * @package humhub.modules_core.tour.controllers
 * @since 0.5
 */
class TourController extends \humhub\components\Controller
{
    public function getAccessRules()
    {
        return [
            ['login']
        ];
    }

    /**
     * Update user settings for completed tours
     */
    public function actionTourCompleted()
    {

        // get section parameter from completed tour
        $section = Yii::$app->request->post('section');

        if (!in_array($section, Yii::$app->params['tour']['acceptableNames'])) {
            return;
        }

        // set tour status to seen for current user
        Yii::$app->getModule('tour')->settings->user()->set($section, 1);
    }

    public function actionHidePanel()
    {
        // set tour status to seen for current user
        Yii::$app->getModule('tour')->settings->user()->set('hideTourPanel', 1);
    }

    /**
     *  This is a special case, because we need to find a space to start the tour
     *
     * @return \yii\web\Response
     * @throws HttpException
     */
    public function actionStartSpaceTour()
    {

        $space = null;

        // Loop over all spaces where the user is member
        foreach (Membership::getUserSpaces() as $space) {
            if ($space->isAdmin() && !$space->isArchived()) {
                // If user is admin on this space, it´s the perfect match
                break;
            }
        }

        if ($space === null) {
            // If user is not member of any space, try to find a public space
            // to run tour in
            $space = Space::findOne(['and', ['!=', 'visibility' => Space::VISIBILITY_NONE], ['status' => Space::STATUS_ENABLED]]);
        }

        if ($space === null) {
            throw new HttpException(404, 'Could not find any public space to run tour!');
        }

        return $this->redirect($space->createUrl('/space/space', ['tour' => true]));
    }

    /**
     * Admin Welcome Lightbox
     */
    public function actionWelcome()
    {
        $user = Yii::$app->user->getIdentity();
        $profile = $user->profile;

        if ($user->id == 1 && $user->load(Yii::$app->request->post()) && $user->validate() && $user->save()) {
            if ($profile->load(Yii::$app->request->post()) && $profile->validate() && $profile->save()) {
                Yii::$app->getModule('tour')->settings->contentContainer($user)->set("welcome", 1);
                return $this->redirect(['/dashboard/dashboard']);
            }
        }

        return $this->renderAjax('welcome', [
                    'user' => $user
        ]);
    }

}
