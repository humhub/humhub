<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient\interfaces;

/**
 * Marks an AuthClient that authenticates the user via a password supplied
 * through HumHub's login form (as opposed to a redirect-based or
 * challenge-response protocol).
 *
 * Implementations validate the credentials against their backend — e.g.
 * local password hash comparison ({@see \humhub\modules\user\authclient\Password})
 * or an LDAP bind ({@see \humhub\modules\ldap\authclient\LdapAuth}) — and
 * signal whether the credentials were accepted.
 *
 * Sibling concept on the redirect/protocol side: {@see CustomAuth}.
 *
 * @since 1.19
 */
interface PasswordAuth
{
    /**
     * Validate the supplied credentials. Returns true when the backend
     * accepted them, false otherwise.
     *
     * A true return means "credentials are valid" — it does NOT imply that
     * a matching HumHub user already exists. For source-owning clients
     * (e.g. LDAP) the HumHub user may still need to be auto-created by
     * the controller's registration flow. Implementations MUST call
     * {@see \yii\authclient\BaseClient::setUserAttributes()} with the
     * normalised attribute set (at minimum an `id` key) so the downstream
     * lookup in {@see \humhub\modules\user\services\AuthClientService::getUser()}
     * has something to work with.
     */
    public function authenticate(string $username, string $password): bool;
}
