<?php

/**
 * PollFormWidget handles the form to create new polls.
 *
 * @package humhub.modules.polls.widgets
 * @since 0.5
 * @author Luke
 */
class TaskFormWidget extends ContentFormWidget {

    public function renderForm() {

        $this->submitUrl = 'tasks/task/create';
        $this->submitButtonText = Yii::t('TasksModule.base', 'Create');

        $this->form = $this->render('taskForm', array('contentContainer'=>$this->contentContainer), true);
    }

}

?>