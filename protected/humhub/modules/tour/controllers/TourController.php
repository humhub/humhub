<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\tour\controllers;

use Yii;
use yii\web\HttpException;
use humhub\modules\space\models\Space;
use yii\helpers\Url;

/**
 * TourController
 *
 * @author andystrobel
 * @package humhub.modules_core.tour.controllers
 * @since 0.5
 */
class TourController extends \humhub\components\Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
            ]
        ];
    }

    /**
     * Update user settings for completed tours
     */
    public function actionTourCompleted()
    {

        // get section parameter from completed tour
        $section = Yii::$app->request->get('section');

        if (!in_array($section, array('interface', 'administration', 'profile', 'spaces')))
            return;

        // set tour status to seen for current user
        Yii::$app->user->getIdentity()->setSetting($section, 1, "tour");
    }

    /*
     * Update user settings for hiding tour panel on dashboard
     */

    public function actionHidePanel()
    {
        // set tour status to seen for current user
        Yii::$app->user->getIdentity()->setSetting('hideTourPanel', 1, "tour");
    }

    /**
     * This is a special case, because we need to find a space to start the tour
     */
    public function actionStartSpaceTour()
    {

        $space = null;

        // Loop over all spaces where the user is member
        foreach (\humhub\modules\space\models\Membership::GetUserSpaces() as $space) {
            if ($space->isAdmin()) {
                // If user is admin on this space, it´s the perfect match
                break;
            }
        }

        if ($space === null) {
            // If user is not member of any space, try to find a public space
            // to run tour in
            $space = Space::findOne(['!=', 'visibility' => Space::VISIBILITY_NONE]);
        }

        if ($space === null) {
            throw new HttpException(404, 'Could not find any public space to run tour!');
        }

        return $this->redirect($space->createUrl('/space/space', array('tour' => true)));
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
                $user->setSetting("welcome", 1, "tour");
                return $this->redirect(Url::to(['/dashboard/dashboard']));
            }
        }

        return $this->renderAjax('welcome', [
                    'user' => $user
        ]);
    }

}
