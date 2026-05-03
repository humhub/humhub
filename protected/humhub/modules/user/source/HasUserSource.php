<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\source;

/**
 * HasUserSource marks an AuthClient that has an associated UserSource
 * responsible for the lifecycle of users it authenticates.
 *
 * AuthClients implementing this interface (e.g. LdapAuth) will have their
 * UserSource used for createUser() and updateUser() instead of LocalUserSource.
 *
 * OAuth/SAML/Password clients do NOT implement this — users created on their
 * first login fall back to LocalUserSource.
 *
 * @since 1.19
 */
interface HasUserSource
{
    public function getUserSource(): UserSourceInterface;
}
