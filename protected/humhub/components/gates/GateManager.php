<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\gates;

use Yii;
use yii\base\Component;
use yii\web\Application as WebApplication;

/**
 * Collects and evaluates user gates.
 *
 * Modules register their gates through [[EVENT_INIT_GATES]] (see [[GateInitEvent]]).
 * The manager evaluates gates ordered by [[UserGateInterface::getSortOrder()]]; the first
 * open gate that applies to the request wins, while a gate's own route and allowed routes
 * shield the request from all gates with a larger sort order.
 *
 * The all-closed state of cacheable gates is cached in the session, bound to the current
 * gate state version and user. [[invalidate()]] bumps the version so that all running
 * sessions re-evaluate once. See `docs/develop/user-gates.md`.
 *
 * @since 1.19
 */
class GateManager extends Component
{
    /**
     * @event GateInitEvent triggered once per request to collect the gates of all modules
     */
    public const EVENT_INIT_GATES = 'initGates';

    /**
     * Name of the settings entry holding the global gate state version.
     */
    private const SETTING_STATE_VERSION = 'gateStateVersion';

    /**
     * Session key holding the all-closed snapshot (state version + user id).
     */
    private const SESSION_ALL_CLOSED = 'gatesAllClosed';

    /**
     * @var UserGateInterface[] registered gates indexed by id
     */
    private array $gates = [];

    /**
     * @var bool whether EVENT_INIT_GATES has been triggered
     */
    private bool $collected = false;

    /**
     * Returns all registered gates ordered by sort order.
     *
     * @return UserGateInterface[]
     */
    public function getGates(): array
    {
        if (!$this->collected) {
            $this->collected = true;
            $this->trigger(self::EVENT_INIT_GATES, new GateInitEvent(['manager' => $this]));
        }

        $gates = array_values($this->gates);
        usort($gates, fn(UserGateInterface $a, UserGateInterface $b) => $a->getSortOrder() <=> $b->getSortOrder());

        return $gates;
    }

    /**
     * Registers a gate. A gate with the same id replaces the previously registered one.
     */
    public function register(UserGateInterface $gate): void
    {
        $this->gates[$gate->getId()] = $gate;
    }

    /**
     * Removes a registered gate by id, e.g. to replace another module's gate.
     */
    public function deregister(string $id): void
    {
        unset($this->gates[$id]);
    }

    /**
     * Determines the gate that intercepts the given request, or null if the request
     * may pass.
     *
     * Gates are evaluated in sort order. The first open gate that owns the current route
     * (its own flow or an allowed route) ends the evaluation without interception —
     * the user is legitimately inside this gate's flow and gates with a larger sort
     * order must not pull them away. Otherwise the first open gate that applies to the
     * request class wins.
     *
     * @param RequestClass $requestClass classification of the current request
     * @param string $route the requested route, e.g. `dashboard` or `user/auth/logout`
     * @return UserGateInterface|null the intercepting gate or null
     */
    public function findOpenGate(RequestClass $requestClass, string $route): ?UserGateInterface
    {
        $snapshotValid = $this->isAllClosedSnapshotValid();
        $openCacheableGateSeen = false;
        $allGatesEvaluated = true;
        $result = null;

        foreach ($this->getGates() as $gate) {
            $isOpen = ($gate->isCacheable() && $snapshotValid) ? false : $gate->isOpen();
            if (!$isOpen) {
                continue;
            }

            if ($gate->isCacheable()) {
                $openCacheableGateSeen = true;
            }

            if ($this->isGateOwnedRoute($gate, $route)) {
                $allGatesEvaluated = false;
                break;
            }

            if ($gate->appliesTo($requestClass)) {
                $result = $gate;
                $allGatesEvaluated = false;
                break;
            }
        }

        if (!$snapshotValid && $allGatesEvaluated && !$openCacheableGateSeen) {
            $this->storeAllClosedSnapshot();
        }

        return $result;
    }

    /**
     * Bumps the global gate state version so that all sessions discard their all-closed
     * snapshot and re-evaluate the gates once. Call this whenever state changes that
     * could (re)open a gate for already-running sessions, e.g. after publishing updated
     * terms.
     */
    public function invalidate(): void
    {
        Yii::$app->settings->set(self::SETTING_STATE_VERSION, $this->getStateVersion() + 1);
    }

    /**
     * Whether the route belongs to the gate's own flow or its allowed routes.
     * Routes are matched by path segment prefix, so `user/auth` covers
     * `user/auth/logout` but not `user/authother`.
     */
    private function isGateOwnedRoute(UserGateInterface $gate, string $route): bool
    {
        $route = trim($route, '/');
        $gateRoutes = array_merge([$gate->getRoute()[0] ?? ''], $gate->getAllowedRoutes());

        foreach ($gateRoutes as $gateRoute) {
            $gateRoute = trim((string)$gateRoute, '/');
            if ($gateRoute === '') {
                continue;
            }
            if ($route === $gateRoute || str_starts_with($route, $gateRoute . '/')) {
                return true;
            }
        }

        return false;
    }

    private function getStateVersion(): int
    {
        return (int)Yii::$app->settings->get(self::SETTING_STATE_VERSION, 0);
    }

    /**
     * The snapshot is bound to the state version and the current user, so that
     * invalidation and identity switches (login, logout, impersonation) discard it.
     */
    private function getSnapshotValue(): ?string
    {
        if (!Yii::$app instanceof WebApplication) {
            return null;
        }

        $userId = Yii::$app->user->isGuest ? 'guest' : (string)Yii::$app->user->id;

        return $this->getStateVersion() . ':' . $userId;
    }

    private function isAllClosedSnapshotValid(): bool
    {
        $snapshotValue = $this->getSnapshotValue();

        return $snapshotValue !== null && Yii::$app->session->get(self::SESSION_ALL_CLOSED) === $snapshotValue;
    }

    private function storeAllClosedSnapshot(): void
    {
        $snapshotValue = $this->getSnapshotValue();
        if ($snapshotValue !== null) {
            Yii::$app->session->set(self::SESSION_ALL_CLOSED, $snapshotValue);
        }
    }
}
