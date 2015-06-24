<?php

/**
 * UserSpacesWidget lists all public spaces of the user
 *
 * @package humhub.modules_core.user.widget
 * @since 0.5
 * @author Luke
 */
class UserSpacesWidget extends HWidget
{

    public $user;

    public function run()
    {

        $showSpaces = 30;
        $spaces = array();
        $i = 0;

        foreach (SpaceMembership::GetUserSpaces($this->user->id) as $space) {
            if ($space->visibility == Space::VISIBILITY_NONE)
                continue;
            if ($space->status != Space::STATUS_ENABLED)
                continue;
            $i++;

            if ($i > $showSpaces)
                break;

            $spaces[] = $space;
        }

        $this->render('userSpaces', array('spaces' => $spaces));
    }

}

?>
