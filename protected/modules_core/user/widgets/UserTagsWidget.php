<?php

/**
 * UserTagsWidget lists all skills/tags of the user
 *
 * @package humhub.modules_core.user.widget
 * @since 0.5
 * @author andystrobel
 */
class UserTagsWidget extends HWidget
{
    public $user;

    public function run()
    {
        $this->render('userTags', array('user' => $this->user));
    }

}

?>
