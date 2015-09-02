<?php

/**
 * Shows some space statistics in the directory - spaces sidebar.
 *
 * @package humhub.modules_core.directory.views
 * @since 0.5
 * @author Luke
 */
class SpaceStatisticsWidget extends HWidget
{

    /**
     * Executes the widgets
     */
    public function run()
    {
        $statsCountSpaces = Space::model()->count();
        $statsCountSpacesHidden = Space::model()->countByAttributes(array('visibility' => Space::VISIBILITY_NONE));
        $statsSpaceMostMembers = Space::model()->find('id = (SELECT space_id  FROM space_membership GROUP BY space_id ORDER BY count(*) DESC LIMIT 1)');

        // Render widgets view
        $this->render('spaceStats', array(
            'statsSpaceMostMembers' => $statsSpaceMostMembers,
            'statsCountSpaces' => $statsCountSpaces,
            'statsCountSpacesHidden' => $statsCountSpacesHidden,
        ));
    }

}

?>
