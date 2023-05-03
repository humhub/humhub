<?php

namespace humhub\modules\user\widgets;

use humhub\modules\user\models\User;
use humhub\components\Widget;

/**
 * UserWall shows a user as wall entry, e.g. in the search
 */

class UserWall extends Widget
{

    /**
     * @var User $user
     */
    public $user;

    public function run()
    {
        return $this->render('userWall', ['user' => $this->user]);
    }

}
