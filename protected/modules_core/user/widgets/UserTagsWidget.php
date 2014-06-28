<?php

/**
 * UserTagsWidget lists all skills/tags of the user
 *
 * @package humhub.modules_core.user.widget
 * @since 0.5
 * @author andystrobel
 */
class UserTagsWidget extends HWidget {

    public function run() {

        $user = Yii::app()->getController()->getUser();


        $this->render('userTags', array('user' => $user));
    }

}
?>
