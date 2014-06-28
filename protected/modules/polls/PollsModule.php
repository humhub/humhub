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
class PollsModule extends CWebModule {

    /**
     * Inits the Module
     */
    public function init() {

        $this->setImport(array(
            'polls.models.*',
            'polls.behaviors.*',
        ));
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
     * On User delete, also delete all posts
     *
     * @param type $event
     */
    public static function onUserDelete($event) {

        foreach (Content::model()->findAllByAttributes(array('user_id' => $event->sender->id, 'object_model' => 'Poll')) as $content) {
            $content->delete();
        }

        foreach (PollAnswerUser::model()->findAllByAttributes(array('created_by' => $event->sender->id)) as $question) {
            $question->delete();
        }

        return true;
    }

    /**
     * On workspace deletion make sure to delete all posts
     *
     * @param type $event
     */
    public static function onSpaceDelete($event) {
        foreach (Content::model()->findAllByAttributes(array('space_id' => $event->sender->id, 'object_model' => 'Poll')) as $content) {
            $content->delete();
        }
    }

    /**
     * After the module was uninstalled from this workspace.
     * Do Cleanup
     *
     * @param type $event
     */
    public static function onSpaceUninstallModule($event) {
        if ($event->params == 'polls') {
            foreach (Content::model()->findAllByAttributes(array('space_id' => $event->sender->id, 'object_model' => 'Poll')) as $content) {
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
        if ($event->params == 'polls') {

            foreach (Content::model()->findAllByAttributes(array('object_model' => 'Poll')) as $content) {
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
        $integrityChecker->showTestHeadline("Validating Polls Module (" . Poll::model()->count() . " entries)");
    }

}