<?php

/**
 * Shows newly registered users as sidebar widget
 *
 * @package humhub.modules_core.directory.widgets
 * @since 0.11
 * @author Luke
 */
class NewMembersWidget extends HWidget
{

    public $showMoreButton = false;

    /**
     * Executes the widgets
     */
    public function run()
    {

        $newUsers = User::model()->active()->recently(10)->findAll();

        // Render widgets view
        $this->render('newMembers', array(
            'newUsers' => $newUsers, // new users
            'showMoreButton' => $this->showMoreButton
        ));
    }

}

?>
