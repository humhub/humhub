<?php

/**
 * Shows newly created spaces as sidebar widget
 *
 * @package humhub.modules_core.directory.widgets
 * @since 0.11
 * @author Luke
 */
class NewSpacesWidget extends HWidget
{

    public $showMoreButton = false;

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

        $this->render('newSpaces', array(
            'newSpaces' => $newSpaces,
            'showMoreButton' => $this->showMoreButton
        ));
    }

}

?>
