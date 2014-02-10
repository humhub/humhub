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

        $newUsers = User::model()->active()->recently(10)->findAll();

        // Some member stats
        Yii::import('application.modules.mail.models.*');
        $statsTotalUsers = User::model()->count();
        $statsUserOnline = UserHttpSession::model()->count('user_id IS NOT NULL');
        $statsMessageEntries = 0;
        if (Yii::app()->moduleManager->isEnabled('mail'))
            $statsMessageEntries = MessageEntry::model()->count();
        $statsUserFollow = UserFollow::model()->count();

        // Render widgets view
        $this->render('memberStats', array(
            'newUsers' => $newUsers, // new users
            'statsTotalUsers' => $statsTotalUsers,
            'statsUserOnline' => $statsUserOnline,
            'statsMessageEntries' => $statsMessageEntries,
            'statsUserFollow' => $statsUserFollow
        ));
    }

}

?>
