<?php

/**
 * Shows some group statistics in the directory - groups sidebar.
 *
 * @package humhub.modules_core.directory.views
 * @since 0.5
 * @author Luke
 */
class GroupStatisticsWidget extends HWidget {

    /**
     * Executes the widgets
     */
    public function run() {

        $groups = Group::model()->count();
        $users = User::model()->count();
        $statsAvgMembers = $users / $groups;
        $statsTopGroup = Group::model()->find('id = (SELECT group_id  FROM user GROUP BY group_id ORDER BY count(*) DESC LIMIT 1)');

        // Render widgets view
        $this->render('groupStats', array(
            'statsTotalGroups' => $groups,
            'statsAvgMembers' => round($statsAvgMembers, 1),
            'statsTopGroup' => $statsTopGroup,
            'statsTotalUsers' => $users,
        ));
    }

}

?>
