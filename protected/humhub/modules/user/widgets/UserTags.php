<?php

namespace humhub\modules\user\widgets;

use yii\base\Widget;

/**
 * UserTagsWidget lists all skills/tags of the user
 *
 * @package humhub.modules_core.user.widget
 * @since 0.5
 * @author andystrobel
 */
class UserTags extends Widget
{
    public $user;

    public function run()
    {
        return $this->render('userTags', ['user' => $this->user]);
    }

}
