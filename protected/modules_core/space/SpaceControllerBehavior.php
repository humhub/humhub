<?php

/**
 * SpaceControllerBehavior is a controller behavior used for space modules/controllers.
 *
 * @author Luke
 * @package humhub.modules_core.space
 * @since 0.5
 */
class SpaceControllerBehavior extends CBehavior {

    /**
     * Returns the current selected space by parameter guid
     *
     * If space doesnt exists or there a no permissions and exception
     * will thrown.
     *
     * @return Space
     */
    public function getSpace() {

        // Check if current space is already determined
        if (Yii::app()->params['currentSpace']) {
            return Yii::app()->params['currentSpace'];
        }

        // Get Space GUID by parameter
        $guid = Yii::app()->request->getQuery('sguid');
        if ($guid == "") {
            // Workaround for older version
            $guid = Yii::app()->request->getQuery('guid');
        }

        // Try Load the space
        $space = Space::model()->findByAttributes(array('guid' => $guid));
        if ($space == null)
            throw new CHttpException(404, Yii::t('SpaceModule.base', 'Space not found!'));

        // Save users last action on this space
        $membership = $space->getUserMembership(Yii::app()->user->id);
        if ($membership != null) {
            $membership->scenario = 'last_visit';
            $membership->last_visit = new CDbExpression('NOW()');
            $membership->save();
        } else {

            // Super Admin can always enter
            if (!Yii::app()->user->isAdmin()) {
                // Space invisible?
                if ($space->visibility == Space::VISIBILITY_NONE) {
                    // Not Space Member
                    throw new CHttpException(404, Yii::t('SpaceModule.base', 'Space is invisible!'));
                }
            }
        }

        // Delete all pending notifications for this space
        $notifications = Notification::model()->findAllByAttributes(array('space_id' => $space->id, 'user_id' => Yii::app()->user->id), 'seen != 1');
        foreach ($notifications as $n) {
            // Ignore Approval Notifications
            if ($n->class == "SpaceApprovalRequestNotification" || $n->class == "SpaceInviteNotification") {
                continue;
            }
            $n->seen = 1;
            $n->save();
        }

        // Store current space to stash
        Yii::app()->params['currentSpace'] = $space;

        return $space;
    }

}

?>
