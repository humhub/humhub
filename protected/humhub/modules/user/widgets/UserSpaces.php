<?php

namespace humhub\modules\user\widgets;

use humhub\modules\space\models\Space;
use humhub\modules\space\models\Membership;

/**
 * UserSpacesWidget lists all public spaces of the user
 *
 * @package humhub.modules_core.user.widget
 * @since 0.5
 * @author Luke
 */
class UserSpaces extends \yii\base\Widget
{

    public $user;

    public function run()
    {
        $showSpaces = 30;

        $spaces = array();
        $i = 0;

        foreach (Membership::GetUserSpaces($this->user->id) as $space) {
            if ($space->visibility == Space::VISIBILITY_NONE)
                continue;
            if ($space->status != Space::STATUS_ENABLED)
                continue;
            $i++;

            if ($i > $showSpaces)
                break;

            $spaces[] = $space;
        }

        return $this->render('userSpaces', array('spaces' => $spaces));
    }

}

?>
