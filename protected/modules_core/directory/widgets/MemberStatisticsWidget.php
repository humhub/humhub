<?php

/**
 * Shows some membership statistics in the directory - members sidebar.
 *
 * @package humhub.modules_core.directory.views
 * @since 0.5
 * @author Luke
 */
class MemberStatisticsWidget extends HWidget {

    /**
     * Executes the widgets
     */
    public function run() {

          // Some member stats
        Yii::import('application.modules.mail.models.*');
        $statsTotalUsers = User::model()->count();

        $criteria = new CDbCriteria;
        $criteria->group = 'user_id';
        $criteria->condition = 'user_id IS NOT null';
        $statsUserOnline = UserHttpSession::model()->count($criteria);

        $statsMessageEntries = 0;
        if (Yii::app()->moduleManager->isEnabled('mail'))
            $statsMessageEntries = MessageEntry::model()->count();

        $statsUserFollow = UserFollow::model()->countByAttributes(array('object_model'=>'User'));

        // Render widgets view
        $this->render('memberStats', array(
            'statsTotalUsers' => $statsTotalUsers,
            'statsUserOnline' => $statsUserOnline,
            'statsMessageEntries' => $statsMessageEntries,
            'statsUserFollow' => $statsUserFollow
        ));
    }

}

?>
