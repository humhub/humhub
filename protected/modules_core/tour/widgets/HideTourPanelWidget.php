<?php

/**
 * This widget will add a menu entry to the PanelMenuWidget
 * to remove the tour panel permanently
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.8
 */
class HideTourPanelWidget extends HWidget {

    /**
     * Executes the widget.
     */
    public function run() {
       $this->render('hideTourPanel', array());

    }

}

?>