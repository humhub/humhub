<?php

class TasksModule extends HWebModule
{

    /**
     * Inits the Module
     */
    public function init()
    {
        $this->setImport(array(
            'tasks.*',
            'tasks.models.*',
            'tasks.behaviors.*',
            'wall.*',
        ));
    }

    public function behaviors()
    {

        return array(
            'SpaceModuleBehavior' => array(
                'class' => 'application.modules_core.space.SpaceModuleBehavior',
            ),
        );
    }

    /**
     * On global module disable, delete all created content
     */
    public function disable()
    {
        if (parent::disable()) {
            foreach (Content::model()->findAllByAttributes(array('object_model' => 'Task')) as $content) {
                $content->delete();
            }
            return true;
        }

        return false;
    }

    /**
     * On disabling this module on a space, deleted all module -> space related content/data.
     * Method stub is provided by "SpaceModuleBehavior"
     * 
     * @param Space $space
     */
    public function disableSpaceModule(Space $space)
    {
        foreach (Content::model()->findAllByAttributes(array('space_id' => $space->id, 'object_model' => 'Task')) as $content) {
            $content->delete();
        }
    }

    /**
     * On User delete, delete all task assignments
     * 
     * @param type $event
     */
    public static function onUserDelete($event)
    {

        foreach (TaskUser::model()->findAllByAttributes(array('created_by' => $event->sender->id)) as $task) {
            $task->delete();
        }
        foreach (TaskUser::model()->findAllByAttributes(array('user_id' => $event->sender->id)) as $task) {
            $task->delete();
        }

        return true;
    }

    /**
     * On build of a Space Navigation, check if this module is enabled.
     * When enabled add a menu item
     * 
     * @param type $event
     */
    public static function onSpaceMenuInit($event)
    {

        $space = Yii::app()->getController()->getSpace();

        // Is Module enabled on this space?
        if ($space->isModuleEnabled('tasks')) {
            $event->sender->addItem(array(
                'label' => Yii::t('TasksModule.base', 'Tasks'),
                'group' => 'modules',
                'url' => Yii::app()->createUrl('/tasks/task/show', array('sguid' => $space->guid)),
                'icon' => '<i class="fa fa-check-square"></i>',
                'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'tasks'),
            ));
        }
    }

}
