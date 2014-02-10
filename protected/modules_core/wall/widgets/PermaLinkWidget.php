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
     * Inits the Perma Link
     *
     * Creates a Javascript Variable "wallPermaLinkUrl" with the Permalink Base URL
     */
    public function init() {

        // Add Archive Links to JS
        Yii::app()->clientScript->setJavascriptVariable(
                "wallPermaLinkUrl", Yii::app()->createAbsoluteUrl('//wall/perma/content', array('model' => '-className-', 'id' => '-id-'))
        );
    }

    /**
     * Executes the widget.
     */
    public function run() {

        $this->render('permaLink', array(
            'object' => $this->content,
            'model' => $this->content->contentMeta->object_model,
            'id' => $this->content->contentMeta->object_id,
        ));
    }

}

?>