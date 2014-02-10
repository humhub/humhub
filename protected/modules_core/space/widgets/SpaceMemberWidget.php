<?php

/**
 * This widget will added to the sidebar, when on admin area
 *
 * @author Luke
 * @package humhub.modules_core.space.widgets
 * @since 0.5
 */
class SpaceMemberWidget extends HWidget {

    //public $template = "application.widgets.views.leftNavigation";

    public function run() {

        //$spaceGuid = Yii::app()->getController()->getSpace()->guid;

        $this->render('spaceMembers', array('space' => Yii::app()->getController()->getSpace()));
    }

}

?>