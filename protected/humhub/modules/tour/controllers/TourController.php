<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\tour\controllers;

use humhub\components\Controller;
use humhub\modules\tour\models\TourConfig;
use humhub\modules\user\models\User;
use Yii;

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
        // get page name from completed tour
        $page = Yii::$app->request->post('page');

        if (!TourConfig::isPageAcceptable($page)) {
            return;
        }

        // set tour status to seen for current user
        Yii::$app->getModule('tour')->settings->user()->set($page, true);
    }

    public function actionHidePanel()
    {
        // set tour status to seen for current user
        Yii::$app->getModule('tour')->settings->user()->set('hideTourPanel', true);
    }

    /**
     * Admin Welcome Lightbox
     */
    public function actionWelcome()
    {
        /* @var User $user */
        $user = Yii::$app->user->getIdentity();

        if (
            $user->id === 1
            && $user->load(Yii::$app->request->post())
            && $user->save(true, ['tagsField'])
            && ($profile = $user->profile)
            && $profile->load(Yii::$app->request->post())
            && $profile->save(true, ['firstname', 'lastname', 'title', 'birthday', 'birthday_hide_year', 'phone_work', 'mobile'])
        ) {
            Yii::$app->getModule('tour')->settings->contentContainer($user)->set('welcome', true);
            return $this->redirect(['/dashboard/dashboard']);
        }

        return $this->renderAjax('welcome', [
            'user' => $user,
        ]);
    }
}
