<?php

/**
 * PermaLink for Wall Entries
 *
 * This widget will attached to the WallEntryControlsWidget and displays
 * the "Permalink" Link to the Content Objects.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.5
 */
class PermaLinkWidget extends HWidget {

    /**
     * Content Object with SIContentBehaviour
     * @var type
     */
    public $content;

    /**
     * Executes the widget.
     */
    public function run() {

        $this->render('permaLink', array(
            'object' => $this->content,
            'model' => $this->content->content->object_model,
            'id' => $this->content->content->object_id,
        ));
    }

}

?>