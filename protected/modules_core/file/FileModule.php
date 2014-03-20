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

}