<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\jobs;

use Yii;
use humhub\modules\queue\ActiveJob;
use humhub\modules\user\authclient\interfaces\AutoSyncUsers;

/**
 * AutoSyncUsers
 *
 * When a authclient provider implements the AutoSyncUser interface the syncUsers
 * method is called to fetch and update users.
 * 
 * @since 1.3
 * @author Luke
 */
class SyncUsers extends ActiveJob
{

    /**
     * @inheritdoc
     */
    public function run()
    {
        foreach (Yii::$app->authClientCollection->getClients() as $authClient) {
            if ($authClient instanceof AutoSyncUsers) {
                /**
                 * @var AutoSyncUsers $authClient 
                 */
                $authClient->syncUsers();
            }
        }
    }

}
