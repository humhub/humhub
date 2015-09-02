<?php

/**
 * SpaceSearchResultWidget displays a space inside the search results.
 * The widget will be called by the Space Model getSearchOutput method.
 *
 * @author Luke
 * @package humhub.modules_core.space.widgets
 * @since 0.5
 */
class SpaceSearchResultWidget extends HWidget {

    /**
     * The space object
     *
     * @var Space
     */
    public $space;

    /**
     * Executes the widget.
     */
    public function run() {

        $this->render('searchResult', array(
            'space' => $this->space,
        ));
    }

}

?>
