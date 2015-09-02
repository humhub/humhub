<?php

/**
 * Delete Link for Wall Entries
 *
 * This widget will attached to the WallEntryControlsWidget and displays
 * the "Delete" Link to the Content Objects.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.5
 */
class DeleteLinkWidget extends HWidget {

    /**
     * Object derived from HActiveRecordContent
     *
     * @var type
     */
    public $object = null;

    /**
     * Executes the widget.
     */
    public function run() {
        if ($this->object->content->canDelete()) {
            $this->render('deleteLink', array(
                'model' => $this->object->content->object_model,
                'id' => $this->object->content->object_id
            ));
        }
    }

}

?>