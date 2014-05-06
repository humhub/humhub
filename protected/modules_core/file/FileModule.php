<?php

/**
 * File Module
 *
 * @package humhub.modules_core.file
 * @since 0.5
 */
class FileModule extends CWebModule {

    /**
     * Inits the Module
     */
    public function init() {

        $this->setImport(array(
        ));
    }

    /**
     * On init of the WallEntryAddonWidget, attach the files of the content.
     *
     * @param CEvent $event
     */
    public static function onWallEntryAddonInit($event) {

        $event->sender->addWidget('application.modules_core.file.widgets.ShowFilesWidget', array(
            'modelName' => $event->sender->object->content->object_model,
            'modelId' => $event->sender->object->content->object_id,
                ), array('sortOrder' => 5)
        );
    }

    /**
     * On cron daily run do some cleanup stuff.
     * We delete all files which are not assigned to object_model/object_id
     * within 1 day.
     *
     * @param type $event
     */
    public static function onCronDailyRun($event) {

        $cron = $event->sender;
        
        /**
         * Delete unused files
         */
        $deleteTime = time() - (60 * 60 * 24 * 1); // Older than 1 day
        foreach (File::model()->findAllByAttributes(array(), 'created_at < :date AND (object_model IS NULL or object_model = "")', array(':date' => date('Y-m-d', $deleteTime))) as $file) {
            $file->delete();
        }
    }

}
