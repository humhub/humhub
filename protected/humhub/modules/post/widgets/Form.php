<?php

namespace humhub\modules\post\widgets;

/**
 * This widget is used include the post form.
 * It normally should be placed above a steam.
 *
 * @package humhub.modules_core.post.widgets
 * @since 0.5
 */
class Form extends \humhub\modules\content\widgets\Form
{

    public $submitUrl = '/post/post/post';

    public function renderForm()
    {

        if (\Yii::$app->user->isGuest)
            return;

        $this->form = $this->render('form', array(), true);
    }

}

?>