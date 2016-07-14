<?php

namespace humhub\modules\content\widgets;

class WallEntryLabels extends \yii\base\Widget
{

    /**
     * Content Object with SIContentBehaviour
     * @var type
     */
    public $object;

    /**
     * Executes the widget.
     */
    public function run()
    {
        return $this->render('labels', array(
            'object' => $this->object,
        ));
    }

}

?>
