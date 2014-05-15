<?php

class TasksModule extends CWebModule {

    /**
     * Inits the Module
     */
    public function init() {
        $this->setImport(array(
            'tasks.*',
            'tasks.models.*',
            'tasks.behaviors.*',
            'wall.*',
        ));
    }

    /**
     * On User delete, also delete all tasks 
     * 
     * @param type $event
     */
    public static function onUserDelete($event) {

        foreach (Content::model()->findAllByAttributes(array('created_by' => $event->sender->id, 'object_model' => 'Task')) as $content) {
            $content->delete();
        }

        foreach (Content::model()->findAllByAttributes(array('user_id' => $event->sender->id, 'object_model' => 'Task')) as $content) {
            $content->delete();
        }

        foreach (TaskUser::model()->findAllByAttributes(array('created_by' => $event->sender->id)) as $task) {
            $task->delete();
        }
        foreach (TaskUser::model()->findAllByAttributes(array('user_id' => $event->sender->id)) as $task) {
            $task->delete();
        }

        return true;
    }

    /**
     * On workspace deletion make sure to delete all tasks
     * 
     * @param type $event
     */
    public static function onSpaceDelete($event) {
        foreach (Content::model()->findAllByAttributes(array('space_id' => $event->sender->id, 'object_model' => 'Task')) as $content) {
            $content->delete();
        }
    }

    /**
     * On build of a Space Navigation, check if this module is enabled.
     * When enabled add a menu item
     * 
     * @param type $event
     */
    public static function onSpaceMenuInit($event) {

        $space = Yii::app()->getController()->getSpace();
        
        // Is Module enabled on this workspace?
        if ($space->isModuleEnabled('tasks')) {
            $event->sender->addItem(array(
                'label' => Yii::t('TasksModule.base', 'Tasks'),
                'url' => Yii::app()->createUrl('/tasks/task/show', array('sguid' => $space->guid)),
                'icon' => '<i class="fa fa-check-square"></i>',
                'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'tasks'),
            ));
        }
    }

    /**
     * After the module was uninstalled from a workspace.
     * Do Cleanup
     * 
     * @param type $event
     */
    public static function onSpaceUninstallModule($event) {
        if ($event->params == 'tasks') {
            foreach (Content::model()->findAllByAttributes(array('space_id' => $event->sender->id, 'object_model' => 'Task')) as $content) {
                $content->delete();
            }
        }
    }

    /**
     * After the module was disabled globally
     * Do Cleanup
     * 
     * @param type $event
     */
    public static function onDisableModule($event) {
        if ($event->params == 'tasks') {
            foreach (Content::model()->findAllByAttributes(array('object_model' => 'Task')) as $content) {
                $content->delete();
            }
        }
    }

    /**
     * On run of integrity check command, validate all module data
     * 
     * @param type $event
     */
    public static function onIntegrityCheck($event) {

        $integrityChecker = $event->sender;
        $integrityChecker->showTestHeadline("Validating Tasks Module (" . Task::model()->count() . " entries)");
    }

}