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
        // Keyset ("seek") pagination: the dropdown list is ordered by
        // aggregate columns (group_seen, group_created_at), not by
        // notification.id or a row count, so the client sends back the
        // sort-key tuple of the last loaded group rather than an offset or an
        // id. This stays correct even if new notifications are inserted while
        // the user is scrolling — unlike offset-based paging, where such an
        // insert shifts every later page and produces duplicates. See
        // Notification::loadMore() for details.
        $cursor = null;
        $cursorSeen = Yii::$app->request->get('cursorSeen');
        $cursorCreatedAt = Yii::$app->request->get('cursorCreatedAt');
        $cursorLastId = Yii::$app->request->get('cursorLastId');
        if ($cursorSeen !== null && $cursorCreatedAt !== null && $cursorLastId !== null) {
            $cursor = [
                'seen' => (int) $cursorSeen,
                'createdAt' => $cursorCreatedAt,
                'lastId' => (int) $cursorLastId,
            ];
        }

        $notifications = Notification::loadMore($cursor);
        $nextCursor = null;

        $output = "";
        foreach ($notifications as $notification) {
            try {
                $baseModel = $notification->getBaseModel();

                if (!$baseModel || !$baseModel->validate()) {
                    throw new IntegrityException('Invalid base model found for notification');
                }

                $output .= $baseModel->render();
                $nextCursor = $notification->getPagingCursor();
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
            'output' => $output,
            'counter' => count($notifications),
            'cursor' => $nextCursor,
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
