<?php

/**
 * SpaceModule provides all space related classes & functions.
 *
 * @author Luke
 * @package humhub.modules_core.space
 * @since 0.5
 */
class SpaceModule extends HWebModule
{

    public $isCoreModule = true;

    public function init()
    {
        $this->setImport(array(
            'space.models.*',
            'space.forms.*',
            'space.controllers.*',
            'space.widgets.*',
        ));
    }

    /**
     * On rebuild of the search index, rebuild all space records
     *
     * @param type $event
     */
    public static function onSearchRebuild($event)
    {
        foreach (Space::model()->findAll() as $obj) {
            if ($obj->visibility != Space::VISIBILITY_NONE)
                HSearch::getInstance()->addModel($obj);
            print "s";
        }
    }

    /**
     * On User delete, also delete his space related stuff
     *
     * @param type $event
     */
    public static function onUserDelete($event)
    {

        $user = $event->sender;

        // Check if the user owns some spaces
        foreach (SpaceMembership::GetUserSpaces($user->id) as $space) {
            if ($space->isSpaceOwner($user->id)) {
                throw new CHttpException(500, Yii::t('SpaceModule.base', 'Could not delete user who is a space owner! Name of Space: {spaceName}', array('spaceName' => $space->name)));
            }
        }

        // Cancel all space memberships
        foreach (SpaceMembership::model()->findAllByAttributes(array('user_id' => $user->id)) as $membership) {
            $membership->space->removeMember($user->id);
        }

        // Cancel all space invites by the user
        foreach (SpaceMembership::model()->findAllByAttributes(array('originator_user_id' => $user->id, 'status' => SpaceMembership::STATUS_INVITED)) as $membership) {
            $membership->space->removeMember($membership->user_id);
        }

        return true;
    }

}
