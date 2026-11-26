# Health check

**Since 1.20.** Core reports its own health through the console and over HTTP, for container probes
(Docker, Kubernetes), load balancers and monitoring (Uptime Kuma, Zabbix, Icinga, Nagios). Core decides
what "live" and "ready" mean; consumers only read a stable, versioned output format.

## Levels

Levels are cumulative: `ready` includes `live`, `all` includes `ready`.

| Level | Question | Typical consumer reaction on failure |
|---|---|---|
| `live` | Is this process broken so that a restart helps? | Kubernetes restarts the container |
| `ready` | Can this instance serve users now? | No traffic, the container keeps running |
| `all` | Is everything in order? | Monitoring alert only |

`live` never depends on the database: a database outage must not restart every application container.
For plain Docker (one `HEALTHCHECK`, also used by Compose `depends_on: condition: service_healthy`) use `ready`.

## State and status

`state` is the lifecycle position and is informational: `unavailable`, `invalid`, `not-installed`,
`database-configured`, `database-created`, `installed`, `maintenance`. New values may be added.

`status` is the health and drives exit and HTTP codes: `ok`, `warning`, `unknown`, `failure` (in this order of
severity). The status of a report is the worst status of its checks.

A not yet installed instance is `ready` with status `ok`, so the installer stays reachable behind an
orchestrator. Alert on `state` if you want to know about it. Maintenance mode is `ready: ok` with
`state: maintenance`, users get the maintenance page.

## Checks

| Check | Level | Notes |
|---|---|---|
| `filesystem:runtime`, `filesystem:assets`, `filesystem:logs` | live | writable; assets and logs may be missing if the parent is writable; logs only when outside runtime |
| `database` | ready | `SELECT 1` |
| `installation` | ready | lifecycle state; explains a not completed installation or maintenance mode |
| `migrations:core` | ready | pending core migrations: failure |
| `migrations:module` | ready | pending migrations of enabled modules: warning |
| `filesystem:uploads` | all | local data mount only |
| `filesystem:modules` | all | warning only |
| `cron` | all | warning after `cronWarningAge`, failure after `cronFailureAge` |
| `queue` | all | database queue only |
| `document-root` | all | legacy entry script: warning, exposed installation root: failure |
| `maintenance` | all | warning while enabled |
| `module:<moduleId>:<id>` | all | added by modules, at most warning |

When the database fails, checks that need it are reported as `unknown` (`skipped: database unavailable`).

## Console

```
yii health/status [--check=live|ready|all] [--format=text|json] [--fail-on=warning|failure]
```

Exit codes: 0 ok, 1 warning, 2 failure, 3 unknown (Nagios). `--fail-on=failure` only uses 0 (ok, warning) and
1 (failure, unknown), as Docker reserves 2:

```dockerfile
HEALTHCHECK CMD php yii health/status --check=ready --fail-on=failure
```

## HTTP

| Request | Answer |
|---|---|
| `GET /health/live`, `GET /health/ready` without token | `{"format": 1, "status": "...", "state": "..."}` |
| any endpoint with a valid token | full report |
| any endpoint with an invalid token | 401 |
| `GET /health` without token | 401 |
| any endpoint, client blocked after too many failed attempts | 429, also with a valid token |

HTTP 200 for ok and warning, 503 for failure and unknown. Responses are `no-store`, set no cookie and start no
session. Failed token attempts are counted per client IP (`Yii::$app->request->getUserIP()`) and rate limited
(policy `health.auth`, 10 per minute and 100 per hour). A blocked client gets 429 for the rest of the block, also
with a valid token. The block only applies to requests that carry a Bearer token: requests without an
`Authorization` header, or with another scheme such as `Basic`, are never counted and never blocked.

Use `/health/ready` for uptime monitors: `/health` answers "is everything in order" and returns 503 for any
failure, e.g. cron not running for a day.

The token is a secret of at least 32 characters, sent as `Authorization: Bearer <token>`. It is configuration,
not stored in the database, so it also works during a database outage:

```
HUMHUB_CONFIG__COMPONENTS__HEALTH__TOKEN=<random string of 32+ characters>
```

The token must contain letters, e.g. generate it with `openssl rand -hex 32`: a value of digits only is converted
to a number by the environment config and never matches.

Without a token, `live` and `ready` still answer minimally.

Operator notes:

- Some Apache setups with PHP-FPM or CGI drop the `Authorization` header. It must be passed through (e.g.
  `CGIPassAuth On`), otherwise a valid token is ignored: `live` and `ready` answer minimally and `/health`
  answers 401.
- Behind a reverse proxy, configure the proxy as trusted (Yii request `trustedHosts`), so that the client IP is
  used for the failed attempt limit. Otherwise all clients share the proxy's IP, and failed attempts of one client
  block token requests of all clients for up to the block duration.

## Probe examples

Kubernetes:

```yaml
livenessProbe:
  httpGet:
    path: /health/live
    port: 80
  periodSeconds: 10
  timeoutSeconds: 5
  failureThreshold: 3
readinessProbe:
  httpGet:
    path: /health/ready
    port: 80
  periodSeconds: 10
  timeoutSeconds: 5
  failureThreshold: 3
```

Docker Compose:

```yaml
healthcheck:
  test: ["CMD", "php", "yii", "health/status", "--check=ready", "--fail-on=failure"]
  interval: 10s
  timeout: 5s
  retries: 3
```

Uptime Kuma should monitor `/health/ready` (HTTP keyword or status), Zabbix and Icinga can run the CLI as a Nagios
plugin.

## Limits

Errors during application bootstrap (e.g. an invalid stored installation state or a module breaking the
bootstrap) happen before the health code runs: HTTP answers 500, the CLI exits 1. With a cold settings cache the
installation state is read from the database even for `live`, so set the liveness timeout above the database
connect timeout.

## Output format

```json
{
  "format": 1,
  "status": "warning",
  "state": "installed",
  "level": "all",
  "version": "1.20.0",
  "time": "2026-11-25T10:41:00Z",
  "notes": ["custom_pages: 1 pending migration(s)"],
  "checks": {
    "database": {"status": "ok", "durationMs": 2},
    "migrations:module": {"status": "warning", "output": "custom_pages: 1 pending migration(s)", "durationMs": 3,
                          "pending": {"custom_pages": ["m261022_110000_fix_space_index_url"]}},
    "queue": {"status": "ok", "output": "3 waiting", "durationMs": 1, "waiting": 3}
  }
}
```

- `durationMs` is present on every executed check. `status`, `output`, `durationMs` and `cachedAt` are reserved; everything else in a check is its own data.
- `output` is a short reason, set for every status but ok. `notes` lists the reasons of all problems and of
  notable ok results (installation not completed, maintenance mode).
- Timestamps are ISO 8601 UTC.

Compatibility: new checks, fields and values do not change `format`. Removing or renaming a check id or a
reserved field, or changing the meaning of a value, does. Consumers must ignore unknown keys and values.

## Configuration

Component `health` (`humhub\components\health\HealthService`):

| Property | Default | |
|---|---|---|
| `token` | `null` | secret for detailed answers |
| `disabledChecks` | `[]` | check ids, e.g. `["queue", "filesystem:modules"]` |
| `cronWarningAge` | `3600` | seconds, `0` turns the cron check off |
| `cronFailureAge` | `86400` | seconds, `0` = never a failure |
| `queueWaitThreshold` | `1200` | seconds |

## Checks of modules

A module adds checks for the level `all` through `HealthService::EVENT_COLLECT_CHECKS`:

```php
// config.php
['class' => HealthService::class, 'event' => HealthService::EVENT_COLLECT_CHECKS,
 'callback' => [Events::class, 'onHealthCollectChecks']],

// Events.php
public static function onHealthCollectChecks(CollectChecksEvent $event): void
{
    $event->addCheck('ldap', new LdapBindCheck());
}

// LdapBindCheck.php
class LdapBindCheck extends HealthCheck
{
    public string $id = 'bind';

    public function run(): HealthResult
    {
        // connect with $this->timeout as the client's own timeout
        return HealthResult::ok(data: ['servers' => 2]);
    }
}
```

Rules enforced by core:

- reported as `module:<moduleId>:<id>`; the module must be enabled, ids must be unique;
- always level `all`, only on an installed instance with a working database;
- status capped at `warning`: a module never makes the platform `failure` or takes it out of rotation;
- an exception or reserved keys in the data make the check `warning` (the `unknown` of a failed check is capped), logged;
- cached for 300 seconds by default (`$cacheTtl`) under the full check id (`module:<moduleId>:<id>`), a cached result
  carries `cachedAt`;
- timeouts are cooperative: PHP cannot interrupt a call, pass `$this->timeout` to your client. A check taking
  longer than its timeout is reported as `warning`.
