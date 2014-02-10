<?php

/**
 * Labels for Wall Entries
 * This widget will attached labels like Sticked, Archived to Wall Entries
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.5
 */
class WallEntryLabelWidget extends HWidget {

    /**
     * Content Object with SIContentBehaviour
     * @var type
     */
    public $object;

    /**
     * Executes the widget.
     */
    public function run() {
        $this->render('label', array(
            'object' => $this->object,
        ));
    }

}

?>