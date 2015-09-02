<?php

/**
 * This widget is used include the post form.
 * It normally should be placed above a steam.
 *
 * @package humhub.modules_core.post.widgets
 * @since 0.5
 */
class PostFormWidget extends ContentFormWidget
{

    public $submitUrl = 'post/post/post';

    public function renderForm()
    {

        if (Yii::app()->user->isGuest)
            return;

        $this->form = $this->render('postForm', array(), true);
    }

}

?>