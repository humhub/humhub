<?php

/**
 * PollWallEntryWidget is used to display a poll inside the stream.
 *
 * This Widget will used by the Poll Model in Method getWallOut().
 *
 * @package humhub.modules.polls.widgets
 * @since 0.5
 * @author Luke
 */
class PollWallEntryWidget extends HWidget {

    public $poll;

    public function run() {

        $this->render('entry', array('poll' => $this->poll,
            'user' => $this->poll->contentMeta->user,
            'space' => $this->poll->contentMeta->getContentBase()));
    }

}

?>