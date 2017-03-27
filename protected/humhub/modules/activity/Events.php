<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity;

use Yii;
use humhub\modules\activity\components\MailSummary;
use humhub\modules\activity\jobs\SendMailSummary;
use humhub\modules\activity\models\Activity;

/**
 * Events provides callbacks to handle events.
 *
 * @author luke
 */
class Events extends \yii\base\Object
{

    /**
     * Handles cron run event to send mail summaries to the users
     *
     * @param \yii\base\ActionEvent $event
     */
    public static function onCronRun($event)
    {
        if (Yii::$app->controller->action->id == 'hourly') {
            Yii::$app->queue->push(new SendMailSummary(['interval' => MailSummary::INTERVAL_HOURY]));
        } elseif (Yii::$app->controller->action->id == 'daily') {
            Yii::$app->queue->push(new SendMailSummary(['interval' => MailSummary::INTERVAL_DAILY]));
            if (date('N') == Yii::$app->getModule('activity')->weeklySummaryDay) {
                Yii::$app->queue->push(new SendMailSummary(['interval' => MailSummary::INTERVAL_WEEKLY]));
            }
        }
    }

    /**
     * On delete of some active record, check if there are related activities and delete them.
     */
    public static function onActiveRecordDelete($event)
    {
        $model = $event->sender->className();
        $pk = $event->sender->getPrimaryKey();

        // Check if primary key exists and is not array (multiple pk)
        if ($pk !== null && !is_array($pk)) {
            foreach (models\Activity::find()->where(['object_id' => $pk, 'object_model' => $model])->all() as $activity) {
                $activity->delete();
            }
        }
    }

    /**
     * Callback to validate module database records.
     *
     * @param Event $event
     */
    public static function onIntegrityCheck($event)
    {
        $integrityController = $event->sender;
        $integrityController->showTestHeadline("Activity Module (" . Activity::find()->count() . " entries)");

        // Loop over all comments
        foreach (Activity::find()->all() as $a) {

            // Check for object_model / object_id
            if ($a->object_model != "" && $a->object_id != "" && $a->getSource() === null) {
                if ($integrityController->showFix("Deleting activity id " . $a->id . " without existing target! (" . $a->object_model . ")")) {
                    $a->delete();
                }
            }

            // Check for moduleId is set
            if ($a->module == "") {
                if ($integrityController->showFix("Deleting activity id " . $a->id . " without module_id!")) {
                    $a->delete();
                }
            }

            // Check Activity class exists
            if (!class_exists($a->class)) {
                if ($integrityController->showFix("Deleting activity id " . $a->id . " class not exists! (" . $a->class . ")")) {
                    $a->delete();
                }
            }
        }
    }

}
