<?php

/**
 * This widget is used include the post form.
 * It normally should be placed above a steam.
 *
 * @package humhub.modules_core.post.widgets
 * @since 0.5
 */
class FrameFormWidget extends FrameBookmarkletFormWidget {

    public $submitUrl = 'post/post/post';
    public $url       = '';

    public function renderForm() {
        $this->form = $this->render('frameForm', array('url' => $this->url), true);
    }

}

?>
