<?php

/**
 * @author Luke
 * @package humhub.modules_core.space.widgets
 * @since 0.5
 */
class YourSpacesMenuWidget extends HWidget {

    public function run() {

        $currentSpace = null;
        $currentSpaceGuid = "";

        if (isset(Yii::app()->params['currentSpace']) && Yii::app()->params['currentSpace'] != null) {
            $currentSpace = Yii::app()->params['currentSpace'];
            $currentSpaceGuid = $currentSpace->guid;
        }

        $this->render('yourSpacesMenu', array(
            'currentSpace' => $currentSpace,
            'currentSpaceGuid' => $currentSpaceGuid,
            'usersSpaces' => SpaceMembership::GetUserSpaces(),
        ));
    }

}

?>
