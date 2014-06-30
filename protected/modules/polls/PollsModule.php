<?php

/**
 * PollsModule is the WebModule for the polling system.
 *
 * This class is also used to process events catched by the autostart.php listeners.
 *
 * @package humhub.modules.polls
 * @since 0.5
 * @author Luke
 */
class PollsModule extends HWebModule
{

    /**
     * Inits the Module
     */
    public function init()
    {

        $this->setImport(array(
            'polls.models.*',
            'polls.behaviors.*',
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
            foreach (Content::model()->findAllByAttributes(array('object_model' => 'Poll')) as $content) {
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
        foreach (Content::model()->findAllByAttributes(array('space_id' => $space->id, 'object_model' => 'Poll')) as $content) {
            $content->delete();
        }
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

        // Is Module enabled on this workspace?
        if ($space->isModuleEnabled('polls')) {
            $event->sender->addItem(array(
                'label' => Yii::t('PollsModule.base', 'Polls'),
                'group' => 'modules',
                'url' => Yii::app()->createUrl('//polls/poll/show', array('sguid' => $space->guid)),
                'icon' => '<i class="fa fa-question-circle"></i>',
                'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'polls'),
            ));
        }
    }

    /**
     * On User delete, delete all poll answers by this user
     *
     * @param type $event
     */
    public static function onUserDelete($event)
    {

        foreach (PollAnswerUser::model()->findAllByAttributes(array('created_by' => $event->sender->id)) as $question) {
            $question->delete();
        }

        return true;
    }

}
