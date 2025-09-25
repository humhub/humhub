<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\tour\controllers;

use humhub\components\Controller;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\tour\Module;
use humhub\modules\user\models\User;
use Yii;
use yii\web\HttpException;
use yii\web\Response;

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
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            ['login'],
        ];
    }

    /**
     * Update user settings for completed tours
     */
    public function actionTourCompleted()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('tour');

        // get section parameter from completed tour
        $section = Yii::$app->request->post('section');

        if (!in_array($section, $module->acceptableNames)) {
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
     * @return Response
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
        /* @var User $user */
        $user = Yii::$app->user->getIdentity();

        if ($user->id == 1
            && $user->load(Yii::$app->request->post())
            && $user->save(true, ['tagsField'])
            && ($profile = $user->profile)
            && $profile->load(Yii::$app->request->post())
            && $profile->save(true, ['firstname', 'lastname', 'title', 'birthday', 'birthday_hide_year', 'phone_work', 'mobile'])
        ) {
            Yii::$app->getModule('tour')->settings->contentContainer($user)->set('welcome', 1);
            return $this->redirect(['/dashboard/dashboard']);
        }

        return $this->renderAjax('welcome', [
            'user' => $user,
        ]);
    }
}
