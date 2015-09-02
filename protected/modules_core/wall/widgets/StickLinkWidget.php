<?php

/**
 * StickLinkWidget for Wall Entries shows a stick link.
 *
 * This widget will attached to the WallEntryControlsWidget and displays
 * the "Stick or Unstick" Link to the Content Objects.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.5
 */
class StickLinkWidget extends HWidget
{

    /**
     * Content Object with SIContentBehaviour
     * @var type
     */
    public $content;

    /**
     * Inits the widget and set some important javascript variables.
     */
    public function init()
    {

        Yii::app()->clientScript->setJavascriptVariable(
                "wallStickLinkUrl", Yii::app()->createUrl('//wall/content/stick', array('className' => '-className-', 'id' => '-id-'))
        );

        Yii::app()->clientScript->setJavascriptVariable(
                "wallUnstickLinkUrl", Yii::app()->createUrl('//wall/content/unstick', array('className' => '-className-', 'id' => '-id-'))
        );
    }

    /**
     * Executes the widget.
     */
    public function run()
    {
        if (!Yii::app()->controller instanceof ContentContainerController || !$this->content->content->canStick()) {
            return;
        }

        $this->render('stickLink', array(
            'object' => $this->content,
            'model' => $this->content->content->object_model,
            'id' => $this->content->content->object_id,
        ));
    }

}

?>