<?php

/**
 * UserSearchResultWidget displays a user inside the search results.
 * The widget will be called by the User Model getSearchOutput method.
 *
 * @package humhub.modules_core.user.widgets
 * @since 0.5
 * @author Luke
 */
class UserSearchResultWidget extends HWidget {

    /**
     * The user object
     *
     * @var User
     */
    public $user;

    /**
     * Executes the widget.
     */
    public function run() {

        $this->render('searchResult', array(
            'user' => $this->user,
        ));
    }

}

?>
