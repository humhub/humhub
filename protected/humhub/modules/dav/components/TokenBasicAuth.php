<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\components;

use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\oauth\services\PersonalAccessTokenService;
use humhub\modules\user\models\User;
use yii\web\Request;

/**
 * HTTP Basic authentication with a personal access token as the password, the user's
 * username or email address as the username — what every WebDAV client can send. The
 * account password is never accepted: not every user has one (SSO, LDAP), and a token can be
 * limited and revoked on its own.
 *
 * The token must be usable as for the API (`GrantService::isUsable()`): not expired, its
 * user enabled, personal access tokens allowed.
 *
 * @since 1.20
 */
final class TokenBasicAuth
{
    /**
     * @return Grant|null the grant of the token, null when the credentials do not authenticate
     */
    public function authenticate(Request $request): ?Grant
    {
        [$username, $token] = $request->getAuthCredentials();

        if (!is_string($token) || !str_starts_with($token, PersonalAccessTokenService::PREFIX)) {
            return null;
        }

        $grant = (new PersonalAccessTokenService())->find($token);
        if ($grant === null || !(new GrantService())->isUsable($grant)) {
            return null;
        }

        return self::namesOwner((string)$username, $grant->user) ? $grant : null;
    }

    /**
     * Whether the username of the credentials is the owner's username or email address, in
     * any case.
     */
    private static function namesOwner(string $username, User $user): bool
    {
        $username = mb_strtolower(trim($username));

        return $username !== ''
            && ($username === mb_strtolower((string)$user->username) || $username === mb_strtolower((string)$user->email));
    }
}
