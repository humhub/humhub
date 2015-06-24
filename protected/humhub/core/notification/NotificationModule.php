<?php

/**
 * NotificationModule
 *
 * @package humhub.modules_core.notification
 * @since 0.5
 */
class NotificationModule extends HWebModule
{

    public $isCoreModule = true;

    public function init()
    {
        $this->setImport(array(
            'notification.controllers.*',
            'notification.models.*',
        ));
    }

    /**
     * On User delete, also delete all posts
     *
     * @param type $event
     */
    public static function onUserDelete($event)
    {

        foreach (Notification::model()->findAllByAttributes(array('user_id' => $event->sender->id)) as $notification) {
            $notification->delete();
        }

        foreach (Notification::model()->findAllByAttributes(array('source_object_model' => 'User', 'source_object_id' => $event->sender->id)) as $notification) {
            $notification->delete();
        }

        foreach (Notification::model()->findAllByAttributes(array('created_by' => $event->sender->id)) as $notification) {
            $notification->delete();
        }

        return true;
    }

    /**
     * On workspace deletion make sure to delete all posts
     *
     * @param type $event
     */
    public static function onSpaceDelete($event)
    {

        foreach (Notification::model()->findAllByAttributes(array('space_id' => $event->sender->id)) as $notification) {
            $notification->delete();
        }
    }

    /**
     * On run of integrity check command, validate all module data
     *
     * @param type $event
     */
    public static function onIntegrityCheck($event)
    {

        $integrityChecker = $event->sender;
        $integrityChecker->showTestHeadline("Validating Notification Module (" . Notification::model()->count() . " entries)");

        foreach (Notification::model()->findAll() as $notification) {

            // Check if Space still exists
            if ($notification->space_id != "" && $notification->space == null) {
                $integrityChecker->showFix("Deleting notification id " . $notification->id . " workspace seems to no longer exist!");
                if (!$integrityChecker->simulate)
                    $notification->delete();
                continue;
            }

            // Check if Space still exists
            $user = User::model()->findByPk($notification->created_by);
            if ($user == null) {
                $integrityChecker->showFix("Deleting notification id " . $notification->id . " user no longer exists!");
                if (!$integrityChecker->simulate)
                    $notification->delete();
                continue;
            }
        }
    }

    /**
     * On run of the cron, do some cleanup stuff.
     * We delete all notifications which are older than 2 month and are seen.
     *
     * @param type $event
     */
    public static function onCronDailyRun($event)
    {

        $cron = $event->sender;

        /**
         * Delete seen notifications which are older than 2 months
         */
        $deleteTime = time() - (60 * 60 * 24 * 31 * 2); // Notifcations which are older as ~ 2 Months
        foreach (Notification::model()->findAllByAttributes(array('seen' => 1), 'created_at < :date', array(':date' => date('Y-m-d', $deleteTime))) as $notification) {
            $notification->delete();
        }
    }

    /**
     * Formatted the notification content before delivery
     *
     * @param string $text
     */
    public static function formatOutput($text)
    {
        $text = HHtml::translateMentioning($text, false);
        $text = HHtml::translateEmojis($text, false);

        return $text;
    }

}
