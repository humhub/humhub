<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification;

use humhub\components\Event;
use humhub\models\RecordMap;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\services\DeliveryService;
use Yii;
use yii\base\BaseObject;
use yii\db\ActiveRecord;
use yii\helpers\Console;

/**
 * Events provides callbacks for all defined module events.
 *
 * There are no delete handlers: notifications of a deleted user, content, container or
 * originator go with the foreign key cascades of the `notification` table, those of any other
 * source record with its {@see \humhub\models\RecordMap} row.
 *
 * @author luke
 */
class Events extends BaseObject
{
    /**
     * Callback to validate module database records.
     *
     * The references of a notification are kept by foreign keys; only the class and a source record
     * whose class became unavailable (e.g. of an uninstalled module) can go stale.
     *
     * @param Event $event
     */
    public static function onIntegrityCheck($event)
    {
        $integrityChecker = $event->sender;
        $integrityChecker->showTestHeadline("Notification Module (" . Notification::find()->count() . " entries)");

        foreach (Notification::find()->each() as $notification) {
            /** @var Notification $notification */
            if (!class_exists($notification->class)) {
                if ($integrityChecker->showFix("Deleting notification id " . $notification->id . " without valid class!")) {
                    $notification->delete();
                }
                continue;
            }

            if ($notification->source_record_id !== null
                && RecordMap::getById((int)$notification->source_record_id, ActiveRecord::class, false) === null) {
                if ($integrityChecker->showFix("Deleting notification id " . $notification->id . " whose source record no longer exists!")) {
                    $notification->delete();
                }
            }
        }
    }

    /**
     * On run of the cron, do some cleanup stuff.
     * We delete all notifications which are older than 2 month and are seen.
     *
     * @param Event $event
     */
    public static function onCronDailyRun($event)
    {
        /* @var Module $module */
        $module = Yii::$app->getModule('notification');

        $controller = $event->sender;

        $controller->stdout('Deleting old notifications... ');

        // Delete seen notifications which are older than 2 months
        self::deleteNotifications(true, $module->deleteSeenNotificationsMonths);

        // Delete unseen notifications which are older than 3 months
        self::deleteNotifications(false, $module->deleteUnseenNotificationsMonths);

        $controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
    }

    /**
     * Hourly: the safety net of the delivery layer, see {@see DeliveryService::sweep()}.
     *
     * @param Event $event
     * @since 1.20
     */
    public static function onCronHourlyRun($event)
    {
        $controller = $event->sender;

        $controller->stdout('Re-queueing overdue notification deliveries... ');
        $pushed = (new DeliveryService())->sweep();
        $controller->stdout('done (' . $pushed . ').' . PHP_EOL, Console::FG_GREEN);
    }

    /**
     * Delete notifications after X months
     *
     * @param bool $seen
     * @param int $months
     * @return int Number of deleted notifications
     */
    private static function deleteNotifications(bool $seen, int $months): int
    {
        return Notification::deleteAll(['AND',
            $seen ? ['IS NOT', 'seen_at', null] : ['seen_at' => null],
            ['<', 'created_at', date('Y-m-d', mktime(0, 0, 0, date('m') - $months))],
        ]);
    }

    public static function onLayoutAddons($event)
    {
        if (Yii::$app->request->isPjax) {
            $event->sender->addWidget(widgets\UpdateNotificationCount::class);
        }
    }
}
