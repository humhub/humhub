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
 * before the local session is destroyed, when this client is the user's
 * current AuthClient (per {@see \humhub\modules\user\components\User::getCurrentAuthClient()}).
 *
 * Return contract:
 *  - {@see Response} → short-circuit the local logout (typically a redirect
 *    SP → IdP for the SLO handshake). The IdP processes the logout and
 *    redirects back to a module-owned callback URL that finalises the
 *    local logout via `Yii::$app->user->logout()`.
 *  - `null` → no remote action required; the local logout proceeds.
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
