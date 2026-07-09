<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient\interfaces;

use yii\web\Response;

/**
 * Marks an AuthClient that handles its own authentication dispatch when the
 * user lands on `/user/auth/external?authclient=<id>`.
 *
 * Use this for protocols that don't fit Yii's built-in OAuth/OpenID families
 * (e.g. SAML, JWT, Passkey/WebAuthn).
 *
 * Dispatch contract: {@see \humhub\modules\user\authclient\AuthAction} calls
 * `handleAuthRequest()` on the client.
 *  - Return a {@see Response} to short-circuit (e.g. redirect SP → IdP, render
 *    an intermediate view).
 *  - Return `null` to signal that authentication is complete and user
 *    attributes are set on the client; `AuthAction::authSuccess()` is then
 *    invoked automatically.
 *
 * Sibling concept on the form-auth side: {@see PasswordAuth}.
 *
 * @since 1.19
 */
interface CustomAuth
{
    public function handleAuthRequest(): ?Response;
}
