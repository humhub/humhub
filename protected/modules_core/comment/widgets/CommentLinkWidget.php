<?php

/**
 * This widget is used to show a comment link inside the wall entry controls.
 *
 * @package humhub.modules_core.comment
 * @since 0.5
 */
class CommentLinkWidget extends HWidget {

    /**
     * Model Name (e.g. Post) to identify which posts we shall show
     *
     * @var String
     */
    public $modelName = "";

    /**
     * The primary key of the model
     *
     * @var String
     */
    public $modelId = "";

    /**
     * Executes the widget.
     */
    public function run() {
        $this->render('commentsLink', array('id' => $this->modelName . "_" . $this->modelId));
    }

}

?>