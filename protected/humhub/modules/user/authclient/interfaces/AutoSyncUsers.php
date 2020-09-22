<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient\interfaces;

/**
 * AutoSyncUsers interface adds the possiblity to automatically update/create users via AuthClient.
 * If this interface is implemented the cron will hourly execute the authclient's 
 * syncronization method.
 * 
 * @author luke
 */
interface AutoSyncUsers
{

    public function syncUsers();
}
