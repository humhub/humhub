# Rate limiting

**Since 1.20.** Core limits how often a caller may use the [HTTP API](concept-api.md). The
limits follow the API's [OAuth scopes](concept-oauth.md#scopes): every `/api/v2` action
already declares its scope, so it is limited without any code of its own.

The mechanism is a general core component (`rateLimiter`): named policies, each with one
or more time windows, counted per subject. This concept wires it to the API only; other
callers can use the same component (see [Extending](#extending)).

In addition, the number of personal access tokens per user is capped.

## Policies and windows

A **policy** is a list of windows, each a limit per time span. A request counts against
the windows of its policy and is refused as soon as one of them is exhausted. With a short
and a long window, a policy allows bursts but caps sustained use:

```php
'burst' => ['limit' => 300, 'span' => 60],    // 300 requests per minute
'long'  => ['limit' => 5000, 'span' => 3600], // 5000 requests per hour
```

The windows are counted in the order of their span, shortest first. Once a window is
exhausted, the longer windows are not charged for that request: a client hammering through
a full burst window does not use up its hourly budget with refused requests. The shorter
windows of an exhausted longer one are charged, which does not matter as the request is
refused anyway.

The windows are fixed: the counter of a window resets when the window starts again
(`floor(now / span)`). This is exact enough to protect the platform. It is not a quota, so
a caller can briefly reach twice a limit across a window boundary.

## The API policies

### Which policy applies

The policy of an `/api/v2` request follows from two things:

- **The channel:** `token` (an OAuth access token or a PAT) or `session` (the platform's
  own frontend, e.g. the Vue islands).
- **The action's scope:** the one declared in `BaseController::$scopes`, resolved as
  `ScopeFilter` does (`scopes[actionId] ?? scopes['*']`). If an action declares a list of
  scopes, the first one counts. An action without a declared scope uses `full`.

The policy is looked up in this order; the first name present in `policies` decides:

1. `<channel>.<scope>`, e.g. `token.content:write`;
2. `<channel>.*:<suffix>`, e.g. `token.*:write` (the suffix after the last colon: `read`
   or `write`);
3. `<channel>.full`.

A policy set to `false` ends the lookup: its requests are unlimited, they do not fall through
to `<channel>.full`. The same holds for a policy whose windows are all invalid (logged once).

Modules therefore get sensible limits for their scopes (`calendar:read`, `mail:write` …)
without declaring anything.

### The subject

What is counted:

- **Token requests:** the grant (`token:<grant id>`). Every app device, connected app and PAT
  has its own budget.
- **Session requests:** the user (`session:<user id>`).
- **Guest requests** (guest-allowed actions): the client IP (`guest:<ip>`), on the
  `session` policies.
- **Other token methods:** a request authenticated by another module's method, e.g. the JWT of
  the `rest` module on `/api/v2`, has no OAuth grant and counts as `session:<user id>` on the
  `session` policies.
- **Failed authentication** (`401`) is not counted, as there is no subject yet. A per-IP limit
  for it would use the component as described under [Extending](#extending).

Counters are kept per subject *and* policy. A grant holding `content:read` and
`content:write` thus has separate budgets for reading and writing.

### Default limits

The defaults leave room for normal use and stop runaway scripts. Session requests get more
room for reading because a page load makes many API calls at once.

| Policy | Burst | Long |
|---|---|---|
| `token.*:read` | 300 / minute | 5000 / hour |
| `token.*:write` | 60 / minute | 1000 / hour |
| `token.full` | 300 / minute | 5000 / hour |
| `session.*:read` | 600 / minute | 10000 / hour |
| `session.*:write` | 120 / minute | 2000 / hour |
| `session.full` | 600 / minute | 10000 / hour |

### Configuration

The component `rateLimiter` (`humhub\components\rateLimit\RateLimiter`) is configured in
`config/common.php`, or per environment variable
(`HUMHUB_CONFIG__COMPONENTS__RATE_LIMITER__…`).

- The configuration is merged into the defaults: policies merge by name, windows by name,
  and window values by key. A window may thus change only its `limit`
  (`'burst' => ['limit' => 30]`); a window with both values replaces it.
- A new policy name adds a policy, e.g. a specific scope.
- `false` disables a policy. A default window cannot be removed on its own, only by
  disabling the whole policy (and, if needed, defining a new one under another name).
- `enabled` = `false` disables rate limiting entirely.

```php
'components' => [
    'rateLimiter' => [
        'policies' => [
            'token.content:write' => [
                'burst' => ['limit' => 30, 'span' => 60],
                'long' => ['limit' => 500, 'span' => 3600],
            ],
            'session.*:read' => false,
        ],
    ],
],
```

As environment variable, the policies are one JSON value (policy names cannot be part of
the variable name):

```
HUMHUB_CONFIG__COMPONENTS__RATE_LIMITER__POLICIES='{"token.content:write":{"burst":{"limit":30,"span":60}},"session.*:read":false}'
```

A window needs a positive integer `limit` and `span`; an invalid window is ignored and logged.
Environment variables carry strings, so `…__ENABLED=false` works as for any component flag.

There is no admin UI.

### Where it is applied

A new filter `humhub\components\api\ApiRateLimitFilter` is attached in
`humhub\components\api\BaseController::behaviors()`, after `authenticator` and
`scopeFilter`. Every v2 controller inherits it, core or module.

- It runs only once authentication has decided the channel.
- A request that `ScopeFilter` refused is not counted, because the filter chain stops
  there.
- A request with a wrong HTTP method (`405`) is counted: the `verbs` filter of a controller
  is merged after the behaviors of `BaseController`.
- Console requests and queue jobs are never limited (there are no API controllers there).

The resolution of an action's scope moves from `ScopeFilter` into a shared static helper
`ScopeFilter::resolveScope(array $scopes, string $actionId): string|array|null`, used by both
filters. It returns the raw declaration; `ApiRateLimitFilter` uses the first entry of a list.

### Response

Every limited response carries the state of its tightest window as the `RateLimit` header
of the IETF draft (`draft-ietf-httpapi-ratelimit-headers`), together with the policy:

```
RateLimit-Policy: "burst";q=300;w=60, "long";q=5000;w=3600
RateLimit: "burst";r=287;t=42
```

- `r` is what remains.
- `t` is the number of seconds until the window resets.

A refused request answers `429 Too Many Requests` with `Retry-After` (the seconds until the
exhausted window resets) and a JSON body:

```json
{"error": "rate_limited", "message": "Too many requests, try again in 42 seconds.", "retryAfter": 42}
```

The first refusal per subject and window is logged as a warning (category `rateLimit`)
with policy, window, subject and route. Later refusals in the same window are not logged.
`RateLimiter` knows nothing of routes; the filter passes the route as the optional log
context of `hit()`.

## Storage

Counting uses the atomic increment of the store wherever one exists:

- **Redis.** If the application cache is `yii\redis\Cache`, its connection is used. Else, if
  a `redis` component is configured, that one is used. Counting is `INCR` with `EXPIRE` on
  the first hit, for all windows of a request in one Lua script: one round trip, atomic and
  shared by every node of a cluster. The script gets the limits too and applies the
  charging rule above itself (it stops after the first window over its limit).
- **Other caches.** The application cache with `add()` / `get()` / `set()`. Under
  concurrency a counter can miss increments, and with `FileCache` every node of a cluster
  counts on its own. Both make the limit looser, never stricter. The admin documentation
  recommends Redis for clusters. With `DummyCache` nothing is counted, so there are no limits.

Keys are `rateLimit:<prefix>:<policy>:<window>:<subject>:<window start>`. They expire with
their window. The prefix keeps instances sharing one Redis or cache apart:

- `RateLimiter::$keyPrefix`, if configured;
- else the `keyPrefix` of the application cache, if the store is that cache or its Redis
  and the prefix is not empty;
- else a short hash of the installation's `secret` setting (a UUID generated by the
  installer, unique per installation), e.g. for a separate `redis` component;
- else the application ID.

The storage sits behind `humhub\components\rateLimit\StorageInterface`
(`increment(array $counters): array`: the counters of one hit as `key => ['ttl', 'limit']`,
in counting order, returning the new values up to the first exceeded counter), implemented
by `RedisStorage` and `CacheStorage`. `RateLimiter::$storage` may name another
implementation.

### Storage failure

A failing store (e.g. Redis unreachable) never refuses a request. After a failure, rate
limiting is paused for 60 seconds (`RateLimiter::$pauseAfterFailure`), so the following
requests do not wait for the store again:

- The pause is kept in the application cache when that cache is not the failing store, e.g.
  a `FileCache` next to a separate `redis` component. It then holds across requests (per
  node, for a local cache).
- Else (the store is the application cache or a Redis cache), the pause holds for the
  current request only, and every request tries the store once.

The failure is logged as an error (category `rateLimit`) once per pause, not per request.
As every attempt waits for the connection to fail, the admin documentation recommends a
short `connectionTimeout` (e.g. 1 second) for the Redis connection; without it, PHP's
`default_socket_timeout` (60 seconds) applies.

## Client IP

Guest subjects use `Yii::$app->request->getUserIP()`. Behind a proxy or load balancer, that
is the proxy's address unless the request component trusts the proxy (`trustedHosts`). The
admin documentation for reverse proxies says so explicitly; without it, all guests share
one budget.

## Component API

`humhub\components\rateLimit\RateLimiter` (application component `rateLimiter`):

- `hit(string $policy, string $subject, ?string $context = null): RateLimitResult` counts one
  request in the windows of the policy (see the charging rule above). It returns whether the
  request is allowed, the window that decides the headers (the one with the fewest remaining
  requests, or the exhausted one) and its `retryAfter`. `$context` is appended to the log line
  of a refusal (the filter passes `route '<route>'`).
  - An unknown or disabled policy, or a disabled limiter, is always allowed and not counted.
  - A storage failure allows the request and pauses rate limiting (see [Storage failure](#storage-failure)).
- `getPolicy(string $name): ?array` returns the configured windows of a policy.

`humhub\components\rateLimit\RateLimitResult`:

- `allowed` (bool);
- `policy` (string), `window` (string), `limit` (int), `remaining` (int);
- `resetsIn` (int, seconds), `retryAfter` (?int);
- `toHeaders(): array` builds the two headers above.

## Extending

Other callers use the same component:

- a web controller without an API, e.g. a form post;
- an endpoint without a scope, e.g. `/oauth/token`;
- a model event.

They define a policy (with defaults in their module, overridable in the same `policies`
configuration) and call `Yii::$app->rateLimiter->hit($policy, $subject)` at the point where
the request is decided. They answer a refusal in their own format (a form error, an OAuth
error). Nothing in this concept depends on the API, except `ApiRateLimitFilter`.

## Personal access tokens per user

New module property and setting of `oauth`, `maxPersonalAccessTokensPerUser` (default `3`,
`0` = unlimited):

- It counts the user's PATs that have not expired.
- A negative value is invalid: it is logged as a warning and the default `3` applies.
- It is configurable via config/ENV (`HUMHUB_CONFIG__MODULES__OAUTH__MAX_PERSONAL_ACCESS_TOKENS_PER_USER`).
- The admin settings page shows it in the "Personal access tokens" group, next to the
  maximum lifetime.

At the limit:

- the token section shows "You have reached the maximum of {n} personal access tokens.
  Revoke one to create a new token." instead of the *Create token* button;
- `PersonalAccessTokenForm` refuses creation with the same message, so a direct POST cannot
  pass it.

Expired tokens do not count, and revoking a token frees its slot immediately.

## Changes

- New `protected/humhub/components/rateLimit/`:
  - `RateLimiter`, `RateLimitResult`;
  - `StorageInterface`, `RedisStorage`, `CacheStorage`.
- New `protected/humhub/components/api/ApiRateLimitFilter`.
- `protected/humhub/config/common.php`: the `rateLimiter` component with the default
  policies above.
- `components/api/BaseController`: the `rateLimit` behavior.
- `components/ApplicationTrait`: `@property-read RateLimiter $rateLimiter`.
- `modules/oauth/components/ScopeFilter`: `resolveScope()` extracted, used by both filters.
- `modules/oauth`:
  - `Module::getMaxPersonalAccessTokensPerUser()`;
  - the `SettingsForm` field;
  - the limit in `PersonalAccessTokenForm` and `TokensSection`.
- Docs:
  - `docs/develop/concept-api.md` (a short "Rate limiting" section linking here);
  - `docs/develop/concept-oauth.md` (the PAT limit);
  - admin documentation on Redis (with a short `connectionTimeout`) and `trustedHosts`;
  - `CHANGELOG.md`.
