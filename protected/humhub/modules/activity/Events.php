<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity;

use humhub\components\ActiveRecord;
use humhub\helpers\ControllerHelper;
use humhub\modules\activity\components\MailSummary;
use humhub\modules\activity\helpers\ActivityHelper;
use humhub\modules\activity\jobs\SendMailSummary;
use humhub\modules\activity\models\Activity;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\widgets\SettingsMenu;
use humhub\modules\content\models\Content;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\user\widgets\AccountMenu;
use Throwable;
use Yii;
use yii\base\ActionEvent;
use yii\base\BaseObject;
use yii\base\Event;
use yii\base\InvalidArgumentException;
use yii\db\ActiveQuery;
use yii\db\AfterSaveEvent;
use yii\db\IntegrityException;
use yii\db\StaleObjectException;

/**
 * Events provides callbacks to handle events.
 *
 * @author luke
 */
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

    /**
     * On delete of some active record, check if there are related activities and delete them.
     *
     * @param Event $event
     */
    public static function onActiveRecordDelete(Event $event)
    {
        if (!($event->sender instanceof ActiveRecord)) {
            throw new InvalidArgumentException('The handler can be applied only to the \humhub\components\ActiveRecord.');
        }

        ActivityHelper::deleteActivitiesForRecord($event->sender);
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

            // Check for object_model / object_id
            if ($a->object_model != '' && $a->object_id != '') {
                try {
                    $source = $a->getSource();
                } catch (IntegrityException $ex) {
                    if ($integrityController->showFix('Deleting activity id ' . $a->id . ' without existing target! (' . $a->object_model . ')')) {
                        $a->hardDelete();
                    }
                }
            }

            // Check for moduleId is set
            if (empty($a->module) && $integrityController->showFix('Deleting activity id ' . $a->id . ' without module_id!')) {
                $a->hardDelete();
            }

            // Check Activity class exists
            if (!class_exists($a->class) && $integrityController->showFix('Deleting activity id ' . $a->id . ' class not exists! (' . $a->class . ')')) {
                $a->hardDelete();
            }
        }
    }

    /**
     * @param AfterSaveEvent $event
     */
    public static function onContentAfterUpdate($event)
    {
        if (!array_key_exists('visibility', $event->changedAttributes)) {
            return;
        }

        /* @var Content $content */
        $content = $event->sender;

        if ($content->object_model === Activity::class) {
            return;
        }

        // Activities should be updated to same visibility as parent Record
        $activitiesQuery = ActivityHelper::getActivitiesQuery($content->getModel());
        if ($activitiesQuery instanceof ActiveQuery) {
            foreach ($activitiesQuery->each() as $activity) {
                /* @var Activity $activity */
                $activity->content->visibility = $content->visibility;
                $activity->content->save();
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

}
