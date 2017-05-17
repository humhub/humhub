<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space;

use Yii;
use humhub\modules\space\models\Space;
use humhub\modules\space\models\Membership;
use yii\web\HttpException;

/**
 * Events provides callbacks for all defined module events.
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
        foreach (Space::find()->batch() as $spaces) {
            foreach ($spaces as $space) {
                Yii::$app->search->add($space);
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
        foreach (Membership::GetUserSpaces($user->id) as $space) {
            if ($space->isSpaceOwner($user->id)) {
                throw new HttpException(500, Yii::t('SpaceModule.base', 'Could not delete user who is a space owner! Name of Space: {spaceName}', array('spaceName' => $space->name)));
            }
        }

        // Cancel all space memberships
        foreach (Membership::findAll(array('user_id' => $user->id)) as $membership) {
            // Avoid activities
            $membership->delete();
        }

        // Cancel all space invites by the user
        foreach (Membership::findAll(array('originator_user_id' => $user->id, 'status' => Membership::STATUS_INVITED)) as $membership) {
            // Avoid activities
            $membership->delete();
        }

        return true;
    }

    public static function onConsoleApplicationInit($event)
    {
        $application = $event->sender;
        $application->controllerMap['space'] = commands\SpaceController::className();
    }

    /**
     * Callback to validate module database records.
     *
     * @param Event $event
     */
    public static function onIntegrityCheck($event)
    {
        $integrityController = $event->sender;

        $integrityController->showTestHeadline("Space Module - Spaces (" . Space::find()->count() . " entries)");
        foreach (Space::find()->all() as $space) {
            foreach ($space->applicants as $applicant) {
                if ($applicant->user == null) {
                    if ($integrityController->showFix("Deleting applicant record id " . $applicant->id . " without existing user!")) {
                        $applicant->delete();
                    }
                }
            }
        }

        $integrityController->showTestHeadline("Space Module - Module (" . models\Module::find()->count() . " entries)");
        foreach (models\Module::find()->joinWith('space')->all() as $module) {
            if ($module->space == null) {
                if ($integrityController->showFix("Deleting space module " . $module->id . " without existing space!")) {
                    $module->delete();
                }
            }
        }

        $integrityController->showTestHeadline("Space Module - Memberships (" . models\Membership::find()->count() . " entries)");
        foreach (models\Membership::find()->joinWith('space')->all() as $membership) {
            if ($membership->space == null) {
                if ($integrityController->showFix("Deleting space membership " . $membership->space_id . " without existing space!")) {
                    $membership->delete();
                }
            }
            if ($membership->user == null) {
                if ($integrityController->showFix("Deleting space membership " . $membership->user_id . " without existing user!")) {
                    $membership->delete();
                }
            }
        }
    }

}
