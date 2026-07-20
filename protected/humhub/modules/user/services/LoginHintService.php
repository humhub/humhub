<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

use Yii;

/**
 * Carries the identity entered in Step 1 of the two-step login flow across
 * the redirect to an external auth client (SAML, OIDC, …), so the client can
 * forward it to the IdP as a non-binding login hint (e.g. the widely
 * supported `login_hint` query parameter) and the IdP can pre-fill its own
 * login mask.
 *
 * Stored is the resolved user's e-mail address — not the raw form input —
 * because IdPs typically know their users by e-mail/UPN rather than by the
 * HumHub username. The hint travels via the session (never via URL) and is
 * consumed exactly once, so a stale hint can't leak into a later,
 * unrelated IdP redirect (e.g. a manual click on the SSO login button).
 *
 * @since 1.19
 */
final class LoginHintService
{
    private const SESSION_KEY = 'auth.login.loginHint';

    public function set(string $hint): void
    {
        if ($hint === '') {
            return;
        }
        Yii::$app->session->set(self::SESSION_KEY, $hint);
    }

    /**
     * Returns the stored hint and removes it from the session, or null when
     * no hint is waiting.
     */
    public function consume(): ?string
    {
        $hint = Yii::$app->session->get(self::SESSION_KEY);
        Yii::$app->session->remove(self::SESSION_KEY);

        return is_string($hint) && $hint !== '' ? $hint : null;
    }

    public function clear(): void
    {
        Yii::$app->session->remove(self::SESSION_KEY);
    }
}
