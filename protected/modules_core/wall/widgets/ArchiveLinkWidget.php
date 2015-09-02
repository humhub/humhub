<?php

/**
 * StickLink for Wall Entries
 *
 * This widget will attached to the WallEntryControlsWidget and displays
 * the "Stick or Unstick" Link to the Content Objects.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.5
 */
class ArchiveLinkWidget extends HWidget {

    /**
     * Content Object with SIContentBehaviour
     * @var type
     */
    public $content;

    /**
     * Inits the Link Widget
     *
     * Registers some Javascript Variables
     */
    public function init() {

        Yii::app()->clientScript->setJavascriptVariable(
                "wallArchiveLinkUrl", Yii::app()->createUrl('//wall/content/archive', array('className' => '-className-', 'id' => '-id-'))
        );
        Yii::app()->clientScript->setJavascriptVariable(
                "wallUnarchiveLinkUrl", Yii::app()->createUrl('//wall/content/unarchive', array('className' => '-className-', 'id' => '-id-'))
        );
    }

    /**
     * Executes the widget.
     */
    public function run() {
        $this->render('archiveLink', array(
            'object' => $this->content,
            'model' => $this->content->content->object_model,
            'id' => $this->content->content->object_id,
        ));
    }

}

?>