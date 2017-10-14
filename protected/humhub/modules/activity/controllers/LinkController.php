<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\activity\models\Activity;

/**
 * LinkController provides link informations about an Activity via JSON.
 *
 * @author luke
 */
class LinkController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'guestAllowedActions' => ['info']
            ]
        ];
    }

    /**
     * Returns the link for the given activity.
     */
    public function actionIndex()
    {
        $activityId = Yii::$app->request->get('id');
        $activity = Activity::findOne(['id' => $activityId]);

        if ($activity !== null && $activity->content->canView()) {
            $this->redirect($activity->getActivityBaseClass()->getUrl());
        }
    }

}
