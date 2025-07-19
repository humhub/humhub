<?php

namespace humhub\modules\content\widgets;

use yii\base\Widget;

class WallEntryBadges extends Widget
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
        return $this->render('badges', [
            'object' => $this->object,
        ]);
    }

}
