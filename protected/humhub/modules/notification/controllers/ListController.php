<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\controllers;

use Exception;
use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\notification\models\Notification;
use Throwable;
use Yii;
use yii\db\IntegrityException;
use yii\web\HttpException;

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
    protected function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY],
        ];
    }

    /**
     * Returns a List of all notifications for an user
     * @throws Throwable
     */
    public function actionIndex()
    {
        $notifications = Notification::loadMore(Yii::$app->request->get('from', 0));
        $lastEntryId = 0;

        $output = "";
        foreach ($notifications as $notification) {
            try {
                $baseModel = $notification->getBaseModel();

                if (!$baseModel || !$baseModel->validate()) {
                    throw new IntegrityException('Invalid base model found for notification');
                }

                $output .= $baseModel->render();
                $lastEntryId = $notification->id;
                $notification->update();
            } catch (IntegrityException $ie) {
                $notification->delete();
                Yii::warning('Deleted inconsistent notification with id ' . $notification->id . '. ' . $ie->getMessage());
            } catch (Exception $e) {
                Yii::error('Could not display notification: ' . $notification->id . '(' . $e . ')');
            }
        }

        $this->asJson([
            'newNotifications' => Notification::findUnseen()->count(),
            'lastEntryId' => $lastEntryId,
            'output' => $output,
            'counter' => count($notifications),
        ]);
    }

    /**
     * Marks all notifications as seen
     * @throws HttpException
     */
    public function actionMarkAsSeen()
    {
        $this->forcePostRequest();

        $count = Notification::updateAll(['seen' => 1], ['user_id' => Yii::$app->user->id]);

        return $this->asJson([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Returns a JSON array which contains
     * - Number of new / unread notification
     *
     * @return array JSON array
     * @throws Throwable
     */
    public static function getUpdates(): array
    {
        return ['newNotifications' => Notification::findUnseen()->count()];
    }
}
