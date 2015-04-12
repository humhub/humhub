<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * UserModuleEvents
 *
 * @since 0.11.2
 * @author luke
 */
class UserModuleEvents
{

    /**
     * On run of integrity check command, validate all user data
     *
     * @param type $event
     */
    public static function onIntegrityCheck($event)
    {

        $integrityChecker = $event->sender;

        $integrityChecker->showTestHeadline("Validating User Module (" . User::model()->count() . " entries)");

        foreach (User::model()->findAll() as $u) {

            $profile = $u->getProfile();
            if ($profile == null || $profile->isNewRecord) {
                $integrityChecker->showWarning("No profile table record found for " . $u->username);
            }

            if ($u->wall_id == "") {
                $wall = new Wall();
                $wall->object_model = 'User';
                $wall->object_id = $u->id;
                $wall->save();

                $u->wall_id = $wall->id;
                $u->save();
                $integrityChecker->showFix("Created wall table entry for " . $u->username);
            }
        }
    }

    /**
     * On rebuild of the search index, rebuild all user records
     *
     * @param type $event
     */
    public static function onSearchRebuild($event)
    {

        foreach (User::model()->findAll() as $obj) {
            HSearch::getInstance()->addModel($obj);
            print "u";
        }
    }

}
