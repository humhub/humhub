<?php

namespace humhub\modules\space\widgets;

use humhub\modules\space\models\Space;
use humhub\components\Widget;

/**
 * Wall shows a space as wall entry, e.g. in the search
 */

class Wall extends Widget
{
    /*
     * @var Space $space
     */
    public $space;

    public function run()
    {
        return $this->render('spaceWall', ['space' => $this->space]);
    }

}
