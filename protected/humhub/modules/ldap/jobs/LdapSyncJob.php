<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ldap\jobs;

use humhub\modules\ldap\source\LdapUserSource;
use humhub\modules\user\services\UserSourceService;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * LdapSyncJob triggers a full LDAP user sync via every registered LdapUserSource.
 *
 * @since 1.19
 */
class LdapSyncJob extends BaseObject implements JobInterface
{
    public function execute($queue): void
    {
        foreach (UserSourceService::getCollection()->getUserSources() as $source) {
            if ($source instanceof LdapUserSource) {
                $source->syncUsers();
            }
        }
    }
}
