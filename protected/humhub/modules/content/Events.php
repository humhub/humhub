<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content;

use Yii;
use humhub\modules\content\models\Content;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\user\models\User;
use humhub\commands\CronController;
use humhub\models\Setting;
use yii\helpers\Console;
use yii\base\Exception;

/**
 * Events provides callbacks to handle events.
 * 
 * @author luke
 */
class Events extends \yii\base\Object
{

    public static function onUserDelete($event)
    {
        $user = $event->sender;

        models\WallEntry::deleteAll(['wall_id' => $user->wall_id]);
        models\Wall::deleteAll(['id' => $user->wall_id]);
        foreach (Content::findAll(['user_id' => $user->id]) as $content) {
            $content->delete();
        }

        return true;
    }

    public static function onSpaceDelete($event)
    {
        $space = $event->sender;

        models\WallEntry::deleteAll(['wall_id' => $space->wall_id]);
        models\Wall::deleteAll(['id' => $space->wall_id]);
        foreach (Content::findAll(['space_id' => $space->id]) as $content) {
            $content->delete();
        }

        return true;
    }

    /**
     * Callback to validate module database records.
     *
     * @param Event $event
     */
    public static function onIntegrityCheck($event)
    {
        $integrityController = $event->sender;

        $integrityController->showTestHeadline("Content Module - Wall Entries " . models\WallEntry::find()->count() . " entries)");
        foreach (models\WallEntry::find()->joinWith('content')->each() as $w) {
            if ($w->content === null) {
                if ($integrityController->showFix("Deleting wall entry id " . $w->id . " without assigned wall entry!")) {
                    $w->delete();
                }
            }
        }

        $integrityController->showTestHeadline("Content Objects (" . Content::find()->count() . " entries)");
        foreach (Content::find()->all() as $content) {
            if ($content->user == null) {
                if ($integrityController->showFix("Deleting content id " . $content->id . " of type " . $content->object_model . " without valid user!")) {
                    $content->delete();
                }
            }
            if ($content->getPolymorphicRelation() == null) {
                if ($integrityController->showFix("Deleting content id " . $content->id . " of type " . $content->object_model . " without valid content object!")) {
                    $content->delete();
                }
            }
            if ($content->space_id != "" && $content->space == null) {
                if ($integrityController->showFix("Deleting content id " . $content->id . " without valid space!")) {
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
        $stackWidget->addWidget(widgets\EditLink::className(), ['content' => $content, 'wallEntryWidget' => $stackWidget->wallEntryWidget]);
        $stackWidget->addWidget(widgets\NotificationSwitchLink::className(), ['content' => $content]);
        $stackWidget->addWidget(widgets\PermaLink::className(), ['content' => $content]);
        $stackWidget->addWidget(widgets\StickLink::className(), ['content' => $content]);
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

        $users = User::find()->distinct()->joinWith(['httpSessions', 'profile'])->where(['user.status' => User::STATUS_ENABLED]);
        $totalUsers = $users->count();
        $done = 0;
        $mailsSent = 0;
        $defaultLanguage = Yii::$app->language;

        Console::startProgress($done, $totalUsers, 'Sending update e-mails to users... ', false);
        foreach ($users->each() as $user) {

            if ($user->email === "") {
                continue;
            }


            // Check user should receive an email
            Yii::$app->user->switchIdentity($user);
            if ($user->language != "") {
                Yii::$app->language = $user->language;
            } else {
                Yii::$app->language = $defaultLanguage;
            }

            $notifications = Yii::$app->getModule('notification')->getMailUpdate($user, $interval);
            $activities = Yii::$app->getModule('activity')->getMailUpdate($user, $interval);

            if ((is_array($notifications) && isset($notifications['html']) && $notifications['html'] != "") || (is_array($activities) && isset($activities['html']) && $activities['html'] != "")) {

                try {
                    $mail = Yii::$app->mailer->compose([
                        'html' => '@humhub/modules/content/views/mails/Update',
                        'text' => '@humhub/modules/content/views/mails/plaintext/Update'
                    ], [
                        'activities' => (isset($activities['html']) ? $activities['html'] : ''),
                        'activities_plaintext' => (isset($activities['plaintext']) ? $activities['plaintext'] : ''),
                        'notifications' => (isset($notifications['html']) ? $notifications['html'] : ''),
                        'notifications_plaintext' => (isset($notifications['plaintext']) ? $notifications['plaintext'] : ''),
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
                } catch (\Swift_SwiftException $ex) {
                    Yii::error('Could not send mail to: ' . $user->email . ' - Error:  ' . $ex->getMessage());
                } catch (Exception $ex) {
                    Yii::error('Could not send mail to: ' . $user->email . ' - Error:  ' . $ex->getMessage());
                }
            }

            Console::updateProgress( ++$done, $totalUsers);
        }

        Console::endProgress(true);
        $controller->stdout('done - ' . $mailsSent . ' email(s) sent.' . PHP_EOL, \yii\helpers\Console::FG_GREEN);
    }

    /**
     * On rebuild of the search index, rebuild all user records
     *
     * @param type $event
     */
    public static function onSearchRebuild($event)
    {
        foreach (Content::find()->all() as $content) {
            $contentObject = $content->getPolymorphicRelation();
            if ($contentObject instanceof \humhub\modules\search\interfaces\Searchable) {
                Yii::$app->search->add($contentObject);
            }
        }
    }

    /**
     * After a components\ContentActiveRecord was saved
     * 
     * @param \yii\base\Event $event
     */
    public static function onContentActiveRecordSave($event)
    {
        if ($event->sender instanceof \humhub\modules\search\interfaces\Searchable) {
            Yii::$app->search->update($event->sender);
        }
    }

    /**
     * After a components\ContentActiveRecord was deleted
     * 
     * @param \yii\base\Event $event
     */
    public static function onContentActiveRecordDelete($event)
    {
        if ($event->sender instanceof \humhub\modules\search\interfaces\Searchable) {
            Yii::$app->search->delete($event->sender);
        }
    }

}
