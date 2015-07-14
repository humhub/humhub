<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content;

use Yii;
use humhub\modules\content\models\Content;
use humhub\modules\user\models\User;
use humhub\commands\CronController;
use humhub\models\Setting;
use yii\helpers\Console;

/**
 * Description of Events
 *
 * @author luke
 */
class Events extends \yii\base\Object
{

    public static function onUserDelete($event)
    {
        $user = $event->sender;

        models\WallEntry::deleteAll(['wall_id' => $user->wall_id]);

        foreach (Content::findAll(['user_id' => $this->id]) as $content) {
            $content->delete();
        }
        foreach (Content::findAll(['created_by' => $this->id]) as $content) {
            $content->delete();
        }

        return true;
    }

    /**
     * On run of integrity check command, validate all wall data
     *
     * @param type $event
     */
    public static function onIntegrityCheck($event)
    {

        $integrityChecker = $event->sender;

        $integrityChecker->showTestHeadline("Wall Module (" . models\WallEntry::find()->count() . " entries)");
        foreach (models\WallEntry::find()->joinWith('content')->each() as $w) {
            if ($w->content === null) {
                if ($integrityChecker->showFix("Deleting wall entry id " . $w->id . " without assigned wall entry!")) {
                    $w->delete();
                }
            }
        }

        $integrityChecker->showTestHeadline("Content Objects (" . Content::find()->count() . " entries)");
        foreach (Content::find()->all() as $content) {
            if ($content->user == null) {
                if ($integrityChecker->showFix("Deleting content id " . $content->id . " of type " . $content->object_model . " without valid user!")) {
                    $content->delete();
                }
            }
            if ($content->getPolymorphicRelation() == null) {
                if ($integrityChecker->showFix("Deleting content id " . $content->id . " of type " . $content->object_model . " without valid content object!")) {
                    $content->delete();
                }
            }
        }
    }

    /**
     * On init of WallEntryControlsWidget add some default widgets to it.
     *
     * @param CEvent $event
     */
    public static function onWallEntryControlsInit($event)
    {
        $stackWidget = $event->sender;
        $content = $event->sender->object;

        $stackWidget->addWidget(widgets\DeleteLink::className(), ['content' => $content]);
        $stackWidget->addWidget(widgets\EditLink::className(), ['content' => $content]);
        //$stackWidget->addWidget(widgets\NotificationSwitchLink::className(), ['content' => $content]);
        $stackWidget->addWidget(widgets\PermaLink::className(), ['content' => $content]);
        $stackWidget->addWidget(widgets\ArchiveLink::className(), ['content' => $content]);
    }

    /**
     * On init of the WallEntryAddonWidget, attach the wall entry links widget.
     *
     * @param CEvent $event
     */
    public static function onWallEntryAddonInit($event)
    {
        $event->sender->addWidget(widgets\WallEntryLinks::className(), array(
            'object' => $event->sender->object,
            'seperator' => "&nbsp;&middot;&nbsp;",
            'template' => '<div class="wall-entry-controls">{content}</div>',
                ), array('sortOrder' => 10)
        );
    }

    public static function onCronRun($event)
    {
        $controller = $event->sender;

        $interval = "";
        if (Yii::$app->controller->action->id == 'hourly') {
            $interval = CronController::EVENT_ON_HOURLY_RUN;
        } elseif (Yii::$app->controller->action->id == 'daily') {
            $interval = CronController::EVENT_ON_DAILY_RUN;
        } else {
            return;
        }

        $users = User::find()->joinWith(['httpSessions', 'profile'])->where(['status' => User::STATUS_ENABLED]);
        $totalUsers = $users->count();
        $done = 0;
        $mailsSent = 0;
        $defaultLanguage = Yii::$app->language;

        Console::startProgress($done, $totalUsers, 'Sending update e-mails to users... ', false);
        foreach ($users->each() as $user) {

            // Check user should receive an email
            Yii::$app->user->switchIdentity($user);
            if ($user->language != "") {
                Yii::$app->language = $user->language;
            } else {
                Yii::$app->language = $defaultLanguage;
            }

            $notifications = Yii::$app->getModule('notification')->getMailUpdate($user, $interval);
            $activities = Yii::$app->getModule('activity')->getMailUpdate($user, $interval);

            if ($notifications != "" || $activities != "") {
                $mail = Yii::$app->mailer->compose(['html' => '@humhub/modules/content/views/mails/Update'], [
                    'activities' => $activities,
                    'notifications' => $notifications
                ]);
                $mail->setFrom([Setting::Get('systemEmailAddress', 'mailing') => Setting::Get('systemEmailName', 'mailing')]);
                $mail->setTo($user->email);
                if ($interval == CronController::EVENT_ON_HOURLY_RUN) {
                    $mail->setSubject(Yii::t('base', "Latest news"));
                } else {
                    $mail->setSubject(Yii::t('base', "Your daily summary"));
                }
                $mail->send();

                $mailsSent++;
            }

            Console::updateProgress(++$done, $totalUsers);
        }

        Console::endProgress(true);
        $controller->stdout('done - ' . $mailsSent . ' email(s) sent.' . PHP_EOL, \yii\helpers\Console::FG_GREEN);
    }

}
