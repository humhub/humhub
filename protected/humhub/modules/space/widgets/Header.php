<?php

namespace humhub\modules\space\widgets;

use Yii;
use \yii\base\Widget;

/**
 * This widget will added to the sidebar and show infos about the current selected space
 *
 * @author Andreas Strobel
 * @package humhub.modules_core.space.widgets
 * @since 0.5
 */
class Header extends Widget
{

    public $space;

    public function run()
    {
        return $this->render('header', array(
                    'space' => $this->space,
        ));
    }

}

?>