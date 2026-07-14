# User Gates (Action Interception)

> Since HumHub 1.19. User gates route a user through a mandatory flow — two-factor
> verification, terms acceptance, forced password change, first-use wizards — before the
> request continues to the page the user originally asked for. Gates replace the previous
> pattern of every module hooking `Controller::EVENT_BEFORE_ACTION` with its own redirect
> and exclusion logic.

## Concept

**A gate is an interception where the user returns to their original target afterwards.**
The dispatcher remembers the requested URL, sends the user through the gate's flow (a check
page, a confirmation form, a wizard) and continues to the original page once the gate closes.

Gates are evaluated on **every request**, not only after login. A gate can open mid-session:
an admin publishes updated terms, activates maintenance mode, or the installation reaches its
user limit. "First page after login" is just the special case *"the gate was already open when
the user logged in"*.

A gate is always **global**: while open, it intercepts every route except its own flow and its
explicitly allowed routes. Blocking only *specific* routes while some condition holds (e.g.
registration and invite routes while the user limit is reached) is not a gate — the user is
not meant to continue afterwards; that is access control with an error page.

Use the right tool for the job:

| Use case | Mechanism |
|---|---|
| "Complete flow X, then continue" — 2FA check, terms, password change, wizard | **User gate** |
| "You are not allowed to do this" → 403 / error page, also for single routes | `AccessControl` / `ControllerAccess` |
| Identity switch, layout/asset tweaks, logging per request | plain `EVENT_BEFORE_ACTION` |

## Writing a gate

Extend `humhub\components\gates\UserGate` (or implement `UserGateInterface` directly for full
control). A minimal onboarding wizard gate:

```php
use humhub\components\gates\UserGate;
use Yii;

class WelcomeWizardGate extends UserGate
{
    public function getId(): string
    {
        return 'welcome-wizard';
    }

    public function getSortOrder(): int
    {
        return self::SORT_ONBOARDING;
    }

    public function isOpen(): bool
    {
        return !Yii::$app->user->isGuest
            && !Yii::$app->user->getIdentity()->getSettings()->get('wizardCompleted');
    }

    public function getRoute(): array
    {
        return ['/mymodule/wizard'];
    }
}
```

The base class defaults: no extra allowed routes, applies to full-page and AJAX requests but
not to API requests, cacheable.

### The interface

```php
namespace humhub\components\gates;

interface UserGateInterface
{
    /** Unique id, e.g. 'twofa', 'legal', 'must-change-password'. */
    public function getId(): string;

    /**
     * Evaluation order, ascending. Use the UserGate::SORT_* constants:
     * SORT_MAINTENANCE = 50, SORT_PASSWORD = 100, SORT_SECOND_FACTOR = 200,
     * SORT_LEGAL = 300, SORT_ONBOARDING = 400.
     */
    public function getSortOrder(): int;

    /**
     * Does the current user/session still have to pass this gate?
     * MUST be side-effect free (no code sending, no session writes) —
     * the dispatcher caches the result per session.
     */
    public function isOpen(): bool;

    /** Route of the gate's own flow, e.g. ['/twofa/check']. */
    public function getRoute(): array;

    /**
     * Additional routes that stay reachable while this gate is open.
     * Each entry is an explicit, security-reviewed decision of THIS gate
     * (e.g. ['user/auth/logout'] for the 2FA gate). Routes are matched by
     * path segment prefix, so 'user/auth' covers all auth controller actions.
     */
    public function getAllowedRoutes(): array;

    /** Which request classes does this gate apply to? */
    public function appliesTo(RequestClass $requestClass): bool;

    /**
     * May the dispatcher cache this gate's closed state in the session?
     * Default true. Return false for gates that must take effect instantly
     * on already-running sessions (e.g. maintenance mode) — their isOpen()
     * is evaluated on every request. See "Caching".
     */
    public function isCacheable(): bool;

    /**
     * Called whenever this gate intercepts a request, before the response is
     * generated. Place for side effects that must happen at interception time
     * (e.g. maintenance mode ending the session). Runs on every intercepted
     * request — implementations must be idempotent. Default: no-op.
     */
    public function onIntercept(): void;
}
```

`isOpen()` decides *whether* the user must pass the gate — it must not perform the work of the
gate itself. Sending a verification code, writing session state or logging belongs into the
gate's own controller (the `getRoute()` target), which runs once the user actually arrives
there. Side effects that must happen at *interception* time (before the user reaches the gate
page) belong into `onIntercept()`.

### Registration

Gates are registered through a collect event on the `GateManager` — the same idiom as
`Menu::EVENT_INIT`. Modules use the standard event tuple:

```php
// config.php
'events' => [
    [GateManager::class, GateManager::EVENT_INIT_GATES, [Events::class, 'onGateInit']],
],
```

```php
// Events.php — registration can be conditional
public static function onGateInit(GateInitEvent $event): void
{
    if (TwofaHelper::getDriver() !== null) {
        $event->manager->register(new TwofaGate());
    }
}
```

The manager collects lazily on first use and sorts by `getSortOrder()`. Handlers may also
replace or deregister gates (`$event->manager->deregister('legal')`) — useful for enterprise
overrides.

## Dispatch

Gates are enforced by a single `ActionFilter` on `humhub\components\Controller`, executed after
`AccessControl`. The dispatcher centrally handles — exactly once, instead of once per module:

- console requests, installer state, guests (per gate via `isOpen()`)
- request classification (see below)
- returnUrl bookkeeping, so the user lands on the originally requested page after the funnel
- session caching of gate state (see "Caching")
- **at most one redirect per request**

Per request the dispatcher:

1. Classifies the request (full page / AJAX / API).
2. Iterates gates in `sortOrder`. The first gate that (a) is open, (b) `appliesTo()` the
   request class, and (c) does not own the current route (its `getRoute()` /
   `getAllowedRoutes()`) wins.
3. The winner's `onIntercept()` hook runs, then the action is cancelled and answered
   according to the request class. All remaining gates are skipped for this request.

**Loop freedom by construction:** a gate's own route and allowed routes are automatically
exempt from all gates with a *larger* `sortOrder`, but remain interceptable by *smaller* ones.
The result is a strictly ordered funnel — password → 2FA → terms → wizard → target page. The
2FA gate may intercept the terms page, never vice versa. Cycles are impossible and no module
needs to know any other module's routes.

## Caching

Gate evaluation is designed so that the steady state — all gates closed — costs nothing.
Caching lives in the dispatcher; gates never cache their own state (the pre-1.19 per-module
session flags are exactly what this replaces).

**All-closed snapshot.** When the dispatcher finds every cacheable gate closed, it stores an
*all-closed* flag in the session together with the current gate state version. As long as the
flag matches the version, subsequent requests skip gate evaluation entirely — no `isOpen()`
call is made. Non-cacheable gates (`isCacheable()` returns `false`) are the only exception:
they are evaluated on every request even while the snapshot is valid.

**Open gates are never cached.** While any gate is open there is no snapshot: the user is
inside the funnel, requests are few, and every `isOpen()` runs fresh — so completing a flow
(verifying the 2FA code, accepting the terms) takes effect on the very next request. When the
last gate closes, the dispatcher writes a new snapshot.

**Invalidation by version, not TTL.** `GateManager::invalidate()` bumps a global gate state
version (stored via the settings component, which is cached in memory — the per-request
version compare is effectively free). Modules call it whenever state changes that could
(re)open a gate for running sessions:

```php
// e.g. after an admin publishes updated terms
Yii::$app->gateManager->invalidate();
```

All sessions then re-evaluate once and re-cache. Rare over-invalidation is intentional and
self-healing — one extra evaluation per session is the cost of never serving a stale gate.
This fixes a real weakness of the ad-hoc session flags it replaces: a plain "checked" flag is
never re-evaluated, so e.g. newly published terms did not reach already-logged-in sessions
until re-login.

The snapshot reflects `isOpen()` of **all** cacheable gates, regardless of whether the current
request would actually be intercepted (request class, gate-owned routes): any open gate
prevents the all-closed snapshot.

## Request classification

Machine endpoints never receive a 302 to an HTML page. The dispatcher answers according to the
request class:

| Request class | Gate response |
|---|---|
| `RequestClass::FullPage` — browser navigation (GET, accepts `text/html`) | 302 to the gate route |
| `RequestClass::Ajax` — XHR / fetch / PJAX / live polling | 401 + JSON `{gate, url}` plus an `X-Redirect` header — `yii.js` redirects the top-level window on it |
| `RequestClass::Api` — machine request (content negotiation without `text/html`) | 403 + JSON `{gate, message}` — if the gate applies to API requests at all |

Most gates do not apply to `Api` requests (the base class default): flows like 2FA are enforced
when the API credential is *issued*, not on every call made with it. A gate that must also hold
for API traffic (e.g. forced password change) opts in via `appliesTo()`.

## Security semantics

Gate exemptions are **gate-specific**, never a shared generic flag. An action that must stay
reachable during an open legal check (e.g. account deletion) is not automatically safe to
expose while a security gate such as 2FA is open. Each entry in `getAllowedRoutes()` is an
explicit decision of the gate that grants it.

The legacy flag `Controller::$doNotInterceptActionIds` / `isNotInterceptedAction()` is
deprecated. Its remaining legitimate meaning — "technical endpoint, never redirect" (live
polling, file downloads) — is covered by request classification; it must not be used as a
gate exemption.

## Core gates

| Gate | sortOrder | Provided by |
|---|---|---|
| `maintenance-mode` | 50 | `user` module (replaces `ControllerAccess::RULE_MAINTENANCE_MODE`; the forced logout of non-admins happens in the gate's `onIntercept()`) |
| `must-change-password` | 100 | `user` module (replaces `ControllerAccess::RULE_MUST_CHANGE_PASSWORD`) |

Module-provided gates: `twofa` (200), `legal` (300), first-use wizards (400).

## Migrating legacy interceptors

Modules that intercept via `Controller::EVENT_BEFORE_ACTION` keep working: the gate dispatcher
runs first and sets `$event->handled` when it intercepts, so legacy handlers do not overwrite
its redirect. To migrate a legacy interceptor:

1. Move the "does the user have to pass?" check into `isOpen()` — free of side effects.
2. Move route exclusions of *other* modules' pages into the trash: ordering by `sortOrder`
   replaces them. Keep only genuinely own decisions as `getAllowedRoutes()`.
3. Register the gate via `GateManager::EVENT_INIT_GATES` and delete the
   `EVENT_BEFORE_ACTION` tuple.
4. Bump `humhub.minVersion` to `1.19` in `module.json`.

Until a module is migrated, its legacy handler should guard against double interception:

```php
if (!$event->isValid || Yii::$app->response->getIsRedirection()) {
    return; // another gate/interceptor already handled this request
}
```
