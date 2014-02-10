<?php

/**
 * Description of ProfileControllerBehavior
 *
 * This Behavior needs to be attached to all controllers which are provides
 * modules to the Profile System.
 *
 * @package humhub.modules_core.user
 * @since 0.5
 * @author Luke
 */
class ProfileControllerBehavior extends CBehavior {


    public function getUser() {

        $guid = Yii::app()->request->getQuery('guid');

        if ($guid == "")
            $guid = Yii::app()->request->getQuery('uguid', Yii::app()->user->guid);


        $user = User::model()->findByAttributes(array('guid' => $guid));

        if ($user == null)
            throw new CHttpException(404, Yii::t('base', 'User not found!'));

        if ($user->status == User::STATUS_DELETED)
            throw new CHttpException(404, 'User deleted!');

        if ($user->status == User::STATUS_NEED_APPROVAL)
            throw new CHttpException(404, 'This user account is not approved yet!');

        return $user;
    }

}

?>
