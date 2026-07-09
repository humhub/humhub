<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient\interfaces;

use yii\web\Response;

/**
 * Marks an AuthClient that supports Single Logout — terminating the user's
 * session at the identity provider, not just locally in HumHub.
 *
 * Called by {@see \humhub\modules\user\controllers\AuthController::actionLogout()}
 * when this client is the user's current AuthClient (per
 * {@see \humhub\modules\user\components\User::getCurrentAuthClient()}). The
 * local Yii identity is always cleared right after this call returns —
 * implementations should *not* call `Yii::$app->user->logout()` themselves.
 *
 * Return contract:
 *  - {@see Response} → typically a redirect SP → IdP for the SLO
 *    handshake. The IdP processes the logout and redirects back to a
 *    module-owned callback URL that validates the LogoutResponse and
 *    fully destroys the PHP session. The Yii identity is cleared *before*
 *    the redirect is emitted, so the user is locally logged out the
 *    moment they leave HumHub — even if the IdP never comes back (e.g.
 *    front-channel iframe SLO flows).
 *  - `null` → no remote action required; the standard local logout
 *    proceeds in AuthController.
 *
 * Sibling interfaces: {@see CustomAuth} for the login-side protocol,
 * {@see PasswordAuth} for credential-driven auth.
 *
 * @since 1.19
 */
interface SingleLogout
{
    public function singleLogout(): ?Response;
}
