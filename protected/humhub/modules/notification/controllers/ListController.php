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
use humhub\modules\notification\components\BaseNotification;

/**
 * ListController
 *
 * @since 0.5
 */
class ListController extends Controller
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
     * Returns a List of all notifications for an user
     */
    public function actionIndex()
    {
        Yii::$app->response->format = 'json';

        $query = Notification::findGrouped();

        $maxId = (int) Yii::$app->request->get('from', 0);
        if ($maxId != 0) {
            $query->andWhere(['<', 'id', $maxId]);
        }
        $query->andWhere(['user_id' => Yii::$app->user->id]);
        $query->limit(6);

        $output = "";

        $notifications = $query->all();
        $lastEntryId = 0;
        foreach ($notifications as $notification) {
            $output .= $notification->getClass()->render();
            $lastEntryId = $notification->id;
        }

        return [
            'output' => $output,
            'lastEntryId' => $lastEntryId,
            'counter' => count($notifications)
        ];
    }

    /**
     * Marks all notifications as seen
     */
    public function actionMarkAsSeen()
    {
        Yii::$app->response->format = 'json';
        $count = Notification::updateAll(['seen' => 1], ['user_id' => Yii::$app->user->id]);

        return [
            'success' => true,
            'count' => $count
        ];
    }

    /**
     * Returns new notifications
     */
    public function actionGetUpdateJson()
    {
        Yii::$app->response->format = 'json';

        return $this->getUpdates();
    }

    /**
     * Returns a JSON which contains
     * - Number of new / unread notification
     * - Notification Output for new HTML5 Notifications
     *
     * @return string JSON String
     */
    public static function getUpdates()
    {
        $user = Yii::$app->user->getIdentity();

        $query = Notification::findGrouped()
                ->andWhere(['seen' => 0])
                ->orWhere(['IS', 'seen', new \yii\db\Expression('NULL')])
                ->andWhere(['user_id' => $user->id]);

        $update['newNotifications'] = $query->count();
        $query->andWhere(['desktop_notified' => 0]);

        $update['notifications'] = array();
        foreach ($query->all() as $notification) {
            if (Yii::$app->getModule('notification')->settings->user()->get("enable_html5_desktop_notifications", Yii::$app->getModule('notification')->settings->get('enable_html5_desktop_notifications'))) {
                $update['notifications'][] = $notification->getClass()->render(BaseNotification::OUTPUT_TEXT);
            }
            $notification->desktop_notified = 1;
            $notification->update();
        }

        return $update;
    }

}
