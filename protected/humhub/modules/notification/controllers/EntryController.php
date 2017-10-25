<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\controllers;

use Yii;
use yii\web\HttpException;
use humhub\components\Controller;
use humhub\components\behaviors\AccessControl;
use humhub\modules\notification\models\Notification;
use humhub\components\access\ControllerAccess;

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
    public function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY]
        ];
    }

    /**
     * Redirects to the target URL of the given notification
     */
    public function actionIndex()
    {
        $notificationModel = Notification::findOne(['id' => Yii::$app->request->get('id'), 'user_id' => Yii::$app->user->id]);

        if ($notificationModel === null) {
            throw new HttpException(404, Yii::t('NotificationModule.error','The requested content is not valid or was removed!'));
        }

        $notification = $notificationModel->getClass();

        if ($notification->markAsSeenOnClick) {
            $notification->markAsSeen();
        }

        // Redirect to notification URL
        return $this->redirect($notification->getUrl());
    }

}
