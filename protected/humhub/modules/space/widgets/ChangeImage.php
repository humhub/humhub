<?php

namespace humhub\modules\space\widgets;

/**
 * This widget will added to the sidebar, when on admin area
 *
 * @author Luke
 * @package humhub.modules_core.space.widgets
 * @since 0.5
 */
class SpaceChangeImageWidget extends HWidget
{

    public function run()
    {

        $this->render('changeImage', array(
        ));
    }

}

?>
