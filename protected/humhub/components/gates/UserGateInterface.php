<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\gates;

/**
 * A user gate routes the user through a mandatory flow — a two-factor check page, terms
 * acceptance, a forced password change, a first-use wizard — before the request continues
 * to the page the user originally asked for.
 *
 * Gates are registered through [[GateManager::EVENT_INIT_GATES]] and enforced centrally
 * by the [[GateFilter]] attached to [[\humhub\components\Controller]]. A gate is always
 * global: while open, it intercepts every route except its own flow and its explicitly
 * allowed routes.
 *
 * See `docs/develop/user-gates.md` for the full concept.
 *
 * @since 1.19
 */
interface UserGateInterface
{
    /**
     * @return string unique id, e.g. 'twofa', 'legal', 'must-change-password'
     */
    public function getId(): string;

    /**
     * Evaluation order, ascending. Use the [[UserGate]] `SORT_*` constants:
     * password = 100, second factor = 200, legal = 300, onboarding = 400.
     *
     * A gate's own route and allowed routes are automatically exempt from all gates with
     * a larger sort order, but remain interceptable by smaller ones.
     *
     * @return int
     */
    public function getSortOrder(): int;

    /**
     * Whether the current user/session still has to pass this gate.
     *
     * MUST be free of side effects (no code sending, no session writes) — the manager
     * caches the result per session. The actual work of the gate belongs into the
     * controller behind [[getRoute()]].
     *
     * @return bool
     */
    public function isOpen(): bool;

    /**
     * @return array route of the gate's own flow in Yii format, e.g. ['/twofa/check']
     */
    public function getRoute(): array;

    /**
     * Additional routes that stay reachable while this gate is open. Each entry is an
     * explicit, security-reviewed decision of THIS gate (e.g. 'user/auth/logout' for a
     * two-factor gate). Routes are matched by path segment prefix, so 'user/auth'
     * covers all actions of the auth controller.
     *
     * @return string[]
     */
    public function getAllowedRoutes(): array;

    /**
     * Whether this gate applies to the given request class. Gates that return `false`
     * for a class never intercept such requests — e.g. most gates do not apply to
     * [[RequestClass::Api]] because they are enforced at credential issuance instead.
     *
     * @param RequestClass $requestClass
     * @return bool
     */
    public function appliesTo(RequestClass $requestClass): bool;

    /**
     * Whether the manager may cache this gate's closed state in the session (see the
     * all-closed snapshot in `docs/develop/user-gates.md`). Return `false` for gates
     * that must take effect instantly on already-running sessions; their [[isOpen()]]
     * is evaluated on every request and should therefore be cheap.
     *
     * @return bool
     */
    public function isCacheable(): bool;

    /**
     * Called by the [[GateFilter]] whenever this gate intercepts a request, before the
     * response (redirect / JSON) is generated. Place for side effects that must happen
     * at interception time — e.g. ending the session while maintenance mode is active.
     * Runs on every intercepted request, so implementations must be idempotent.
     * Must not send a response itself.
     */
    public function onIntercept(): void;
}
