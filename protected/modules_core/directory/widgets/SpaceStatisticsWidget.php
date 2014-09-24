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
        $criteria = new CDbCriteria();
        $criteria->join = 'LEFT JOIN space_membership ON t.id=space_membership.space_id AND space_membership.user_id=:userId';
        $criteria->condition = 't.visibility != :visibilityNone OR space_membership.status = :statusMember';
        $criteria->params = array(
            ':userId' => Yii::app()->user->id,
            ':visibilityNone' => Space::VISIBILITY_NONE,
            ':statusMember' => SpaceMembership::STATUS_MEMBER);
        $newSpaces = Space::model()->active()->recently(10)->findAll($criteria);

        $statsCountSpaces = Space::model()->count();
        $statsCountSpacesHidden = Space::model()->countByAttributes(array('visibility' => Space::VISIBILITY_NONE));
        $statsSpaceMostMembers = Space::model()->find('id = (SELECT space_id  FROM space_membership GROUP BY space_id ORDER BY count(*) DESC LIMIT 1)');

        // Render widgets view
        $this->render('spaceStats', array(
            'newSpaces' => $newSpaces, // new workspaces
            'statsSpaceMostMembers' => $statsSpaceMostMembers,
            'statsCountSpaces' => $statsCountSpaces,
            'statsCountSpacesHidden' => $statsCountSpacesHidden,
        ));
    }

}

?>
