<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient\interfaces;

/**
 * SyncAttributes interface allows the possiblitz to specify user attributes which will be automatically 
 * updated on login or by daily cronjob if AutoSyncUsers is enabled.
 * 
 * These attributes are also not writable by user.
 * 
 * @since 1.1
 * @author luke
 */
interface SyncAttributes
{

    /**
     * Returns attribute names which should be synced on login
     * 
     * @return array attribute names to be synced
     */
    public function getSyncAttributes();
}
