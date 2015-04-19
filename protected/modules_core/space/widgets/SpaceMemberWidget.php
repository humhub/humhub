<?php

/**
 * This widget will added to the sidebar, when on admin area
 *
 * @author Luke
 * @package humhub.modules_core.space.widgets
 * @since 0.5
 */
class SpaceMemberWidget extends HWidget
{

    public $space;

    public function run()
    {
        $this->render('spaceMembers', array('space' => $this->space));
    }

}

?>