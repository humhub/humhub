<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\notification\models\Notification;

/**
 * EntryController
 *
 * @since 0.5
 */
class EntryController extends Controller
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
     * Redirects to the target URL of the given notification
     */
    public function actionIndex()
    {
        $notificationModel = Notification::findOne(['id' => Yii::$app->request->get('id'), 'user_id' => Yii::$app->user->id]);

        if ($notificationModel === null) {
            throw new \yii\web\HttpException(404, 'Could not find requested notification!');
        }

        $notification = $notificationModel->getClass();

        if ($notification->markAsSeenOnClick) {
            $notification->markAsSeen();
        }

        // Redirect to notification URL
        return $this->redirect($notification->getUrl());
    }

}
