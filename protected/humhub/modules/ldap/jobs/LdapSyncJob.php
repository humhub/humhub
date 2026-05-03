<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ldap\jobs;

use humhub\modules\ldap\authclient\LdapAuth;
use humhub\modules\user\source\HasUserSource;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * LdapSyncJob triggers a full LDAP user sync for all configured LDAP auth clients.
 *
 * @since 1.19
 */
class LdapSyncJob extends BaseObject implements JobInterface
{
    public function execute($queue): void
    {
        foreach (Yii::$app->authClientCollection->getClients() as $client) {
            if ($client instanceof LdapAuth && $client instanceof HasUserSource) {
                $client->getUserSource()->syncUsers();
            }
        }
    }
}
