<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient\interfaces;

use humhub\modules\user\models\User;

/**
 * Marks an AuthClient that authenticates the user via a password supplied
 * through HumHub's login form (as opposed to a redirect-based or
 * challenge-response protocol).
 *
 * Implementations validate the credentials against their backend — e.g.
 * local password hash comparison ({@see \humhub\modules\user\authclient\Password})
 * or an LDAP bind ({@see \humhub\modules\ldap\authclient\LdapAuth}) — and
 * return the matching HumHub user on success.
 *
 * Sibling concept on the redirect/protocol side: {@see CustomAuth}.
 *
 * @since 1.19
 */
interface PasswordAuth
{
    /**
     * Validate the supplied credentials. Returns the authenticated user on
     * success, null on failure.
     *
     * Implementations are also expected to call {@see setUserAttributes()}
     * with at minimum an `id` key so the Yii auth-client machinery and
     * session-storage flow stay consistent — but the return value is the
     * authoritative contract for callers.
     */
    public function authenticate(string $username, string $password): ?User;
}
