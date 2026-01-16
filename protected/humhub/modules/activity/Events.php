<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity;

use humhub\helpers\ControllerHelper;
use humhub\models\RecordMap;
use humhub\modules\activity\components\MailSummary;
use humhub\modules\activity\jobs\SendMailSummary;
use humhub\modules\activity\models\Activity;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\widgets\SettingsMenu;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\AccountMenu;
use Throwable;
use Yii;
use yii\base\ActionEvent;
use yii\base\BaseObject;
use yii\base\Event;
use yii\db\StaleObjectException;

class Events extends BaseObject
{
    /**
     * Handles cron hourly run event to send mail summaries to the users
     *
     * @param ActionEvent $event
     */
    public static function onCronHourlyRun($event)
    {
        if (static::getModule()->enableMailSummaries) {
            Yii::$app->queue->push(new SendMailSummary(['interval' => MailSummary::INTERVAL_HOURLY]));
        }
    }

    /**
     * Handles cron daily run event to send mail summaries to the users
     *
     * @param ActionEvent $event
     */
    public static function onCronDailyRun($event)
    {
        $module = static::getModule();
        if ($module->enableMailSummaries) {
            Yii::$app->queue->push(new SendMailSummary(['interval' => MailSummary::INTERVAL_DAILY]));
            if (date('w') === (string)$module->weeklySummaryDay) {
                Yii::$app->queue->push(new SendMailSummary(['interval' => MailSummary::INTERVAL_WEEKLY]));
            }
            if (date('j') === (string)$module->monthlySummaryDay) {
                Yii::$app->queue->push(new SendMailSummary(['interval' => MailSummary::INTERVAL_MONTHLY]));
            }
        }
    }

    public static function onAccountMenuInit($event)
    {
        if (static::getModule()->enableMailSummaries) {
            /** @var AccountMenu $menu */
            $menu = $event->sender;

            $menu->addEntry(new MenuLink([
                'label' => Yii::t('ActivityModule.base', 'E-Mail Summaries'),
                'id' => 'account-settings-emailsummary',
                'icon' => 'envelope',
                'url' => ['/activity/user'],
                'sortOrder' => 105,
                'isActive' => ControllerHelper::isActivePath('activity'),
            ]));
        }
    }


    public static function onSettingsMenuInit($event)
    {
        if (static::getModule()->enableMailSummaries) {
            /** @var SettingsMenu $menu */
            $menu = $event->sender;

            $menu->addEntry(new MenuLink([
                'label' => Yii::t('ActivityModule.base', 'E-Mail Summaries'),
                'url' => ['/activity/admin/defaults'],
                'sortOrder' => 300,
                'isActive' => ControllerHelper::isActivePath('activity', 'admin', 'defaults'),
                'isVisible' => Yii::$app->user->can(ManageSettings::class),
            ]));
        }
    }

    /**
     * Callback to validate module database records.
     *
     * @param Event $event
     * @throws Throwable
     * @throws StaleObjectException
     */
    public static function onIntegrityCheck($event)
    {
        $integrityController = $event->sender;
        $integrityController->showTestHeadline('Activity Module (' . Activity::find()->count() . ' entries)');

        // Loop over all comments
        foreach (Activity::find()->each() as $a) {
            /** @var Activity $a */

            // ToDo: Class has all dependencies

            // Check Activity class exists
            if (!class_exists($a->class) && $integrityController->showFix('Deleting activity id ' . $a->id . ' class not exists! (' . $a->class . ')')) {
                $a->hardDelete();
            }
        }
    }

    /**
     * @return Module
     */
    private static function getModule()
    {
        return Yii::$app->getModule('activity');
    }

    public static function onBeforeRecordMapDelete($event)
    {
        /** @var RecordMap $recordMap */
        $recordMap = $event->sender;

        foreach (Activity::findAll(['content_addon_record_id' => $recordMap->id]) as $activity) {
            $activity->delete();
        }

        return true;
    }

    public static function onBeforeContentContainerDelete($event)
    {
        /** @var ContentContainerActiveRecord $record */
        $record = $event->sender;

        foreach (Activity::findAll(['activity.contentcontainer_id' => $record->contentcontainer_id]) as $activity) {
            $activity->delete();
        }

        return true;
    }
    public static function onBeforeContentDelete($event)
    {
        /** @var Content $record */
        $record = $event->sender;

        foreach (Activity::findAll(['content_id' => $record->id]) as $activity) {
            $activity->delete();
        }

        return true;
    }

    public static function onBeforeUserDelete($event)
    {
        /** @var User $record */
        $record = $event->sender;

        foreach (Activity::findAll(['activity.created_by' => $record->id]) as $activity) {
            $activity->delete();
        }

        return true;
    }


}
