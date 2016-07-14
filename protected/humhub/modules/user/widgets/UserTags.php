<?php

namespace humhub\modules\user\widgets;

/**
 * UserTagsWidget lists all skills/tags of the user
 *
 * @package humhub.modules_core.user.widget
 * @since 0.5
 * @author andystrobel
 */
class UserTags extends \yii\base\Widget
{

    public $user;

    public function run()
    {
        return $this->render('userTags', array('user' => $this->user));
    }

}

?>
