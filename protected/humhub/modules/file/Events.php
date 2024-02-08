<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file;

use humhub\components\ActiveRecord;
use humhub\components\behaviors\PolymorphicRelation;
use humhub\modules\file\models\File;
use yii\base\BaseObject;
use yii\base\Event;
use humhub\modules\file\converter\TextConverter;
use yii\helpers\Console;

/**
 * Events provides callbacks to handle events.
 *
 * @author luke
 */
class Events extends BaseObject
{
    /**
     * On init of the WallEntryAddonWidget, attach the files of the content.
     *
     * @param Event $event
     */
    public static function onWallEntryAddonInit($event)
    {
        $event->sender->addWidget(widgets\ShowFiles::class, ['object' => $event->sender->object], ['sortOrder' => 5]);
    }

    /**
     * On cron daily run do some cleanup stuff.
     * We delete all files which are not assigned to object_model/object_id
     * within 1 day.
     *
     * @param Event $event
     */
    public static function onCronDailyRun($event)
    {

        $controller = $event->sender;
        $controller->stdout("Deleting old unassigned files... ");

        // Delete unused files
        $deleteTime = time() - (60 * 60 * 24 * 1); // Older than 1 day
        foreach (File::find()->andWhere(['<', 'created_at', date('Y-m-d', $deleteTime)])->andWhere('(object_model IS NULL or object_model = "")')->all() as $file) {
            $file->delete();
        }

        $controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
    }

    /**
     * Callback to validate module database records.
     *
     * @param Event $event
     */
    public static function onIntegrityCheck($event)
    {
        $integrityController = $event->sender;
        $integrityController->showTestHeadline("File Module (" . File::find()->count() . " entries)");

        foreach (File::find()->each() as $file) {
            if ($file->object_model != "" && $file->object_id != "" && $file->getPolymorphicRelation() === null) {
                if ($integrityController->showFix("Deleting file id " . $file->id . " without existing target!")) {
                    $file->delete();
                }
            }
        }
    }

    /**
     * On delete of a model, check there are files bound to it and delete them
     *
     * @param Event $event
     */
    public static function onBeforeActiveRecordDelete($event)
    {
        /* @var ActiveRecord $record */
        $record = $event->sender;
        $pk = $record->getPrimaryKey();

        // Check if primary key exists and is not array (multiple pk)
        if ($pk !== null && !is_array($pk)) {
            foreach (File::find()->where(['object_id' => $pk, 'object_model' => PolymorphicRelation::getObjectModel($record)])->all() as $file) {
                $file->delete();
            }
        }
    }

    public static function onUserDelete($event)
    {
        foreach (File::findAll(['created_by' => $event->sender->id]) as $file) {
            $file->delete();
        }
        return true;
    }
}
