<?php

/**
 * PollFormWidget handles the form to create new polls.
 *
 * @package humhub.modules.polls.widgets
 * @since 0.5
 * @author Luke
 */
class PollFormWidget extends HWidget {

    public $space;

    public function run() {
        $this->render('form', array('space' => $this->space));
    }

}

?>