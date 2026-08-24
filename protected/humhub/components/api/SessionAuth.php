<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\api;

use humhub\components\Request as HumHubRequest;
use Yii;
use yii\filters\auth\AuthMethod;
use yii\web\ForbiddenHttpException;
use yii\web\Request;
use yii\web\User;

/**
 * Authenticates API requests by the regular HumHub browser session — the method that lets
 * the platform's own frontend (the Vue islands) call the API without a token.
 *
 * Only controllers that opt in get it (see {@see BaseController::$enableSessionAuth}), and
 * it is registered LAST in the chain, so every token method a module contributes takes
 * precedence: a request carrying a valid token authenticates as the token user even when a
 * session cookie is present ("token wins").
 *
 * Security contract:
 *
 * - **CSRF.** State-changing requests (POST/PUT/PATCH/DELETE) additionally require a valid
 *   CSRF token — the `X-CSRF-Token` header (what `humhub.client` sends, fed from the
 *   `csrf-token` meta tag) or the `_csrf` body parameter. Without it, a cookie-authenticated
 *   API would be a CSRF hole. Safe methods are exempt; token-authenticated requests are
 *   never CSRF-checked.
 * - **Allowlist.** Session authentication deliberately bypasses the API user allowlist a
 *   module may implement for its token methods: a session-authenticated call can do nothing
 *   the same user's browser session cannot already do through the normal web controllers,
 *   and the frontend must work for every logged-in user.
 * - **Established sessions only.** Guests stay unauthenticated, and the auto-login
 *   ("remember me") cookie alone does not authenticate an API request — core re-establishes
 *   the session on any regular page load before the frontend issues API calls.
 * - **Gates and impersonation** apply exactly as they do for a browser request, because this
 *   method marks the request via {@see HumHubRequest::$isSessionAuthenticated} — see
 *   {@see \humhub\components\gates\GateFilter::getRequestClass()} and
 *   {@see \humhub\modules\user\components\Impersonation::isActive()}. No special-casing here.
 *
 * @since 1.20
 */
class SessionAuth extends AuthMethod
{
    /**
     * @inheritdoc
     * @param User $user
     * @param Request $request
     * @throws ForbiddenHttpException when a session user issues a state-changing request
     * without a valid CSRF token
     */
    public function authenticate($user, $request, $response)
    {
        // Cheap opt-out: no session cookie and no already-active session (test environment)
        // means there is nothing to restore — pure token clients never touch the session.
        $session = Yii::$app->session;
        if (!$session->getHasSessionId() && !$session->getIsActive()) {
            return null;
        }

        $identity = $this->getSessionIdentity($user);
        if ($identity === null) {
            return null;
        }

        if (!$this->validateCsrfToken($request)) {
            throw new ForbiddenHttpException('Unable to verify your data submission. Session-authenticated modifying requests require a valid CSRF token (X-CSRF-Token header).');
        }

        // Everything that needs to tell "browser session" from "machine client" keys off
        // this flag, because the user component stays session-less for writes (see
        // getSessionIdentity()). Set only for a request that actually authenticates.
        if ($request instanceof HumHubRequest) {
            $request->isSessionAuthenticated = true;
        }

        return $identity;
    }

    /**
     * Restores the identity from the HumHub browser session.
     *
     * {@see BaseController::beforeAction()} configures the API user component session-less
     * (`enableSession = false`) so that token logins can never write into the browser session
     * (`yii\web\User::login()` would otherwise regenerate the session id and rebind the
     * session to the token user). Instead of enabling sessions for the whole request, the
     * session identity is restored through a temporary window here: `yii\web\User::getIdentity()`
     * caches the restored identity on the component, so everything after this call — including
     * `Yii::$app->user` access inside actions — behaves as usual while the component stays
     * session-less for writes.
     *
     * This runs the full `yii\web\User::renewAuthStatus()` machinery: session auth key
     * validation plus the same `authTimeout` / `absoluteAuthTimeout` expiry rules as the web
     * UI (the timeouts are copied from the application's user component in
     * {@see BaseController::beforeAction()}).
     */
    private function getSessionIdentity(User $user)
    {
        $enableSession = $user->enableSession;
        $user->enableSession = true;
        try {
            return $user->getIdentity();
        } finally {
            $user->enableSession = $enableSession;
        }
    }

    /**
     * Validates the CSRF token for state-changing requests without ever minting one; safe
     * methods (GET/HEAD/OPTIONS) always pass.
     *
     * `yii\web\Request::validateCsrfToken()` is deliberately NOT used: it calls
     * `getCsrfToken()`, which — with the CSRF cookie enabled — generates a fresh token and
     * emits a `_csrf` Set-Cookie whenever the request carries none, clobbering the browsing
     * page's real token. Instead the browser's true token is read straight from the `_csrf`
     * cookie (HumHub core default) and compared timing-safely against the client-supplied
     * token; no cookie means no valid token. No API response ever sets a cookie this way.
     */
    private function validateCsrfToken(Request $request): bool
    {
        if (in_array($request->getMethod(), $request->csrfTokenSafeMethods, true)) {
            return true;
        }

        // The `_csrf` cookie holds the raw token; missing cookie ⇒ no valid token.
        $trueToken = $request->getCookies()->getValue($request->csrfParam);
        if (!is_string($trueToken) || $trueToken === '') {
            return false;
        }

        // The client sends the masked token via the X-CSRF-Token header (or the `_csrf` body
        // param), exactly like `humhub.client` does in the browser.
        $clientToken = $request->getCsrfTokenFromHeader() ?? $request->getBodyParam($request->csrfParam);
        if (!is_string($clientToken) || $clientToken === '') {
            return false;
        }

        $security = Yii::$app->security;

        // `unmaskToken()` recovers the raw token from the masked client value for a
        // timing-safe comparison — no token generation, no Set-Cookie.
        return $security->compareString($security->unmaskToken($clientToken), $trueToken);
    }
}
