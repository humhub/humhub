<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file;

use humhub\modules\file\models\File;

/**
 * Events provides callbacks to handle events.
 * 
 * @author luke
 */
class Events extends \yii\base\Object
{

    /**
     * On init of the WallEntryAddonWidget, attach the files of the content.
     *
     * @param CEvent $event
     */
    public static function onWallEntryAddonInit($event)
    {
        $event->sender->addWidget(widgets\ShowFiles::className(), array('object' => $event->sender->object), array('sortOrder' => 5));
    }

    /**
     * On cron daily run do some cleanup stuff.
     * We delete all files which are not assigned to object_model/object_id
     * within 1 day.
     *
     * @param type $event
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

        $controller->stdout('done.' . PHP_EOL, \yii\helpers\Console::FG_GREEN);
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

        foreach (File::find()->all() as $file) {
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
     * @param CEvent $event
     */
    public static function onBeforeActiveRecordDelete($event)
    {

        $model = $event->sender->className();
        $pk = $event->sender->getPrimaryKey();

        // Check if primary key exists and is not array (multiple pk)
        if ($pk !== null && !is_array($pk)) {
            foreach (File::find()->where(['object_id' => $pk, 'object_model' => $model])->all() as $file) {
                $file->delete();
            }
        }
    }

    public static function onUserDelete($event)
    {
        foreach (File::findAll(array('created_by' => $event->sender->id)) as $file) {
            $file->delete();
        }
        return true;
    }

}
