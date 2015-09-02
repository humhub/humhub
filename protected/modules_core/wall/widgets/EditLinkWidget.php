<?php

/**
 * Edit Link for Wall Entries
 *
 * This widget will attached to the WallEntryControlsWidget and displays
 * the "Edit" Link to the Content Objects.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.10
 */
class EditLinkWidget extends HWidget
{

    /**
     * Object derived from HActiveRecordContent
     *
     * @var type
     */
    public $object = null;

    /**
     * Executes the widget.
     */
    public function run()
    {
        if ($this->object->wallEditRoute != "" && $this->object->content->canWrite()) {
            $this->render('editLink', array(
                'id' => $this->object->content->object_id,
                'object' => $this->object,
                'editRoute' => $this->object->wallEditRoute
            ));
        }
    }

}

?>