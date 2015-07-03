<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

namespace humhub\modules\file;

use humhub\modules\file\models\File;

/**
 * FileModuleEvents handles all events described in autostart.php
 *
 * @package humhub.modules_core.file
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
     * On run of integrity check command, validate all module data
     *
     * @param CEvent $event
     */
    public static function onIntegrityCheck($event)
    {
        /*
          $integrityChecker = $event->sender;
          $integrityChecker->showTestHeadline("Validating File Module (" . File::model()->count() . " entries)");

          foreach (File::model()->findAll() as $a) {

          if ($a->object_model != "" && $a->object_id != "" && $a->getUnderlyingObject() === null) {
          $integrityChecker->showFix("Deleting file id " . $a->id . " without existing target!");
          if (!$integrityChecker->simulate)
          $a->delete();
          }
          }
         *
         */
    }

    /**
     * On delete of a model, check there are files bound to it and delete them
     *
     * @param CEvent $event
     */
    public static function onBeforeActiveRecordDelete($event)
    {
        foreach (models\File::find()->where(['object_id' => $event->sender->getPrimaryKey(), 'object_model' => $event->sender->className()])->all() as $file) {
            $file->delete();
        }
    }

}
