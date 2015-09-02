<?php

/**
 * PostSearchResultWidget displays a post inside the search results.
 * The widget will be called by the Post Model getSearchOutput method.
 *
 * @package humhub.modules_core.post.widgets
 * @since 0.5
 * @author Luke
 */
class PostSearchResultWidget extends HWidget {

    /**
     * The post object
     *
     * @var Post
     */
    public $post;

    /**
     * Executes the widget.
     */
    public function run() {

        $this->render('searchResult', array(
            'post' => $this->post,
        ));
    }

}

?>
