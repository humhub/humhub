<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space;

use Yii;
use humhub\modules\space\models\Space;

/**
 * Description of Events
 *
 * @author luke
 */
class Events extends \yii\base\Object
{

    /**
     * On rebuild of the search index, rebuild all space records
     *
     * @param type $event
     */
    public static function onSearchRebuild($event)
    {
        foreach (Space::find()->all() as $obj) {
            if ($obj->visibility != Space::VISIBILITY_NONE) {
                Yii::$app->search->add($obj);
            }
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

    public static function onConsoleApplicationInit($event)
    {

        $application = $event->sender;
        $application->controllerMap['space'] = commands\SpaceController::className();
    }

}
