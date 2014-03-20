<?php

/**
 * PollFormWidget handles the form to create new polls.
 *
 * @package humhub.modules.polls.widgets
 * @since 0.5
 * @author Luke
 */
class PollFormWidget extends ContentFormWidget {

    public function renderForm() {

        $this->submitUrl = 'polls/poll/create';
        $this->submitButtonText = Yii::t('PollsModule.base', 'Ask');

        $this->form = $this->render('pollForm', array(), true);
    }

}

?>