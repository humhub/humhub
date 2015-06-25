<?php

namespace humhub\core\space\widgets;

use Yii;
use \yii\base\Widget;

/**
 * This widget will added to the sidebar, when on admin area
 *
 * @author Luke
 * @package humhub.modules_core.space.widgets
 * @since 0.5
 */
class SpaceMemberWidget extends Widget
{

    public $space;

    public function run()
    {
        return $this->render('spaceMembers', array('space' => $this->space));
    }

}

?>