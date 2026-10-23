# Apps & API access — configuration for administrators

As of 1.20, HumHub has its own OAuth 2.0 server and personal access tokens. They give
programs access to an account through the HTTP API (`/api/v2`) without the user's password:

- **The HumHub app** signs in through the system browser — with every login method the
  network offers (password, LDAP, SSO, second factor) — and receives a token. Customer-specific
  builds of the app ([white-label apps](#white-label-apps)) sign in the same way.
- **Clients** are integrations and agents you register. A user authorizes a client once, on a
  consent page that lists what it may do.
- **Personal access tokens** are static tokens users create for their own scripts and agents
  that do not speak OAuth.

Each of them is a *grant*: one per app installation, connected client or token. A grant can do
at most what its user can do in the web interface, limited further by its *scopes* (e.g.
*Read content, comments and likes*). No module is needed for any of this.

Administrators configure it under *Administration → Users → Settings → Apps & API Access*
(permission *Manage settings*). Users see and end their own grants under *Account → Security &
Privacy → Devices & Access*: the devices of the HumHub app (*Sign out*), connected clients
(*Disconnect*) and their personal access tokens (*Revoke*, *Create token*). Users get a
notification by web and e-mail when a client is connected or a token is created for their
account.

## Settings

The *Settings* tab:

| Setting | Default | Effect |
|---|---|---|
| *Allow the HumHub app to sign in* | on | The app and the white-label apps may sign in. |
| *Additional app IDs* | none | See [White-label apps](#white-label-apps). System administrators only. |
| *Allow clients created by administrators* | off | Clients may be authorized and their grants work. |
| *Allow personal access tokens* | off | Tokens work, and users with the group permission may create them. |
| *Scopes available to personal access tokens* | all read scopes | The upper bound of what a token can do. Empty means no access. |
| *Maximum lifetime of personal access tokens (days)* | 365 | Users choose 7, 30 or 90 days, a year or *never*; longer choices are hidden. 0 means unlimited and offers *never*. |
| *Maximum personal access tokens per user* | 3 | Tokens that have not expired; at the limit a user has to revoke one first. 0 means unlimited. |
| *Refresh token lifetime (days)* | 90 | An app or client that does not refresh its token for this long has to sign in again. Every refresh renews it, so an app in regular use stays signed in. |
| *Maximum lifetime of a grant (days)* | 0 | After this, an app or a connected client has to sign in again, in use or not. Counted from the sign-in; a change applies to later sign-ins. 0 means unlimited. |
| *Revoke access when the password changes* | off | A password change ends all grants of the user, except the app session the change was made in. |

The three *Allow* switches act on **existing** grants as well: switching one off stops every
sign-in or token of that kind immediately, including the app's web views; switching it on
again restores them. The same holds for the scopes: removing a scope from the token scopes
or from a client takes it away from existing grants at once.

A user creating a token confirms their password first (or, without a local password, must have
signed in within the last 15 minutes), because a token bypasses the second factor. Nobody can
create tokens or client secrets while impersonating a user.

### The group permission

Users need the group permission *Create personal access tokens* (*Administration → Users →
Groups → Permissions*), which is not granted to any group by default. While tokens are allowed
but no group has the permission, the settings page shows a warning: nobody can create one.

Taking the permission away only stops creating new tokens; existing tokens keep working. To
stop them, switch *Allow personal access tokens* off or revoke them under *Grants*.

### Scopes

| Scope | Allows |
|---|---|
| *Full access to your account* (`full`) | everything the web interface can do, including endpoints without a scope of their own (e.g. the marketplace) |
| *Read your account and settings* / *Change your settings* | the account and notification settings |
| *Read your notifications* / *Mark notifications as seen* | notifications |
| *Read people and profiles* / *Follow people and manage friendships* | people |
| *Read spaces and memberships* / *Join, leave and follow spaces* | spaces |
| *Read content, comments and likes* / *Write comments, like, upload files* | content |

Modules can add scopes of their own. A scope never grants more than the user's permissions.

## Clients

The *Clients* tab lists the HumHub app (built in, it cannot be edited) and the clients you
added. *Add client*:

- **Name** and **description**, shown to the user on the consent page.
- **Confidential client** — for a server application that can keep a secret. It
  authenticates with its client ID and secret. Without it, the client is *public* (a native
  app, a command line agent) and has no secret; it must use PKCE, as OAuth requires.
- **Redirect URIs**, one per line, matched exactly: `https://` URIs; loopback URIs such as
  `http://127.0.0.1/callback` or `http://[::1]/callback` (any port); and, for public clients
  only, a reverse-domain scheme such as `com.example.app:/callback`. Plain `http://` to other
  hosts is not accepted.
- **Allowed access** — the scopes the client may request. *Full access* can only be set by a
  system administrator.
- **Trusted: skip the consent page** — the user is not asked; the client gets access as soon as
  the user is signed in. System administrators only.
- **Active** — an inactive client cannot be authorized, and its existing grants stop working
  until it is active again.

The client ID is generated. The secret of a confidential client is shown **once**, right after
saving — copy it then. *Regenerate secret* replaces it; the old secret stops working at once.
*Delete* removes the client together with all its grants.

Other holders of *Manage settings* do not see *Full access* and *skip consent*; what a system
administrator set there stays when they edit the client.

The endpoints a client needs are published as OAuth metadata at
`<base URL>/.well-known/oauth-authorization-server` (authorization, token and revocation
endpoint).

## Grants

The *Grants* tab lists every app sign-in, connected client and personal access token of all
users, with created and last-used date. Filter by user (name, username or e-mail), client and
type. *Revoke* ends one grant; *Revoke all* ends every grant matching the current filter —
for example all tokens of one user, or all sign-ins of one client. Revoking cannot be undone:
the app or client has to sign in again, a token is gone.

Disabling or deleting a user also ends their access; deleting a user deletes their grants.

## White-label apps

Customer-specific builds of the HumHub app have their own app ID (iOS bundle ID / Android
package name). They sign in as the HumHub app and are covered by *Allow the HumHub app to sign
in*, but only once their app ID is listed under *Additional app IDs* (one per line). The app
then signs in with the redirect URI `<app ID>:/oauth/callback`. The built-in app
`com.humhub.app` is always allowed.

An app ID must be in reverse-domain form: at least two dot-separated parts of lower-case
letters, digits and hyphens, starting with a letter (`com.acme.intranet`). Underscores are
not possible; such an app uses another scheme without them, and that is what you enter.

Only system administrators can change the list. It can also be set in `config/common.php`,
which wins over the page (it is then shown read-only):

```php
return [
    'modules' => [
        'oauth' => [
            'appIds' => ['com.acme.app', 'de.example.intranet'],
        ],
    ],
];
```

or as an environment variable, a JSON list or a comma-separated string:
`HUMHUB_CONFIG__MODULES__OAUTH__APP_IDS='["com.acme.app","de.example.intranet"]'`.

## Keys

Tokens are signed with an RSA key pair, and authorization codes and refresh tokens are
encrypted with an encryption key. All three are generated on installation (or on the update
to 1.20) and stored in the database, so every node of a cluster uses the same ones.

To keep them outside the database, set them in `config/common.php`, as the value itself or as
a `file://` path:

```php
return [
    'modules' => [
        'oauth' => [
            'privateKey' => 'file:///run/secrets/humhub-oauth-private.pem',
            'publicKey' => 'file:///run/secrets/humhub-oauth-public.pem',
            'encryptionKey' => getenv('HUMHUB_OAUTH_ENCRYPTION_KEY') ?: null,
        ],
    ],
];
```

**Rotating the key pair:** `php protected/yii oauth/rotate-key` generates a new pair and prints
its key id. Access tokens signed with the old key stop working; apps and clients get a new one
with their refresh token, so users stay signed in. Refresh tokens and personal access tokens
are not affected. With the key pair set in the configuration, the command refuses: replace the
keys there.

Changing the encryption key, by contrast, makes every refresh token unusable: all apps and
clients have to sign in again.

## Rate limiting

`/api/v2` is rate limited per grant (each app device, client and token has its own budget),
per user for the platform's own pages, and per IP address for guests. Each request counts
against the policy of the scope it needs, in a short and a long window:

| Policy | Requests | Per minute | Per hour |
|---|---|---|---|
| `token.*:read` | tokens, reading | 300 | 5000 |
| `token.*:write` | tokens, writing | 60 | 1000 |
| `token.full` | tokens, endpoints that need `full` | 300 | 5000 |
| `session.*:read` | the web interface and guests, reading | 600 | 10000 |
| `session.*:write` | the web interface and guests, writing | 120 | 2000 |
| `session.full` | the web interface and guests, other endpoints | 600 | 10000 |

Reading and writing are counted separately. A module's scopes ending in `:read` or `:write`
(`calendar:read`, …) fall under the `*:read` and `*:write` policies. A single scope can get a policy of its own, e.g.
`token.content:write`.

Override them in `config/common.php` — only the values you set change, `false` switches a
policy off:

```php
return [
    'components' => [
        'rateLimiter' => [
            'policies' => [
                'token.*:read' => ['burst' => ['limit' => 600]],
                'token.content:write' => [
                    'burst' => ['limit' => 30, 'span' => 60],
                    'long' => ['limit' => 500, 'span' => 3600],
                ],
                'session.*:read' => false,
            ],
            // 'enabled' => false, // no rate limiting at all
        ],
    ],
];
```

As environment variables, the policies are one JSON value:

```sh
HUMHUB_CONFIG__COMPONENTS__RATE_LIMITER__POLICIES='{"token.*:read":{"burst":{"limit":600}},"session.*:read":false}'
HUMHUB_CONFIG__COMPONENTS__RATE_LIMITER__ENABLED=false
HUMHUB_CONFIG__MODULES__OAUTH__MAX_PERSONAL_ACCESS_TOKENS_PER_USER=10
```

The last one sets the [token limit per user](#settings); the settings page then shows it
read-only.

**Storage.** The counters live in Redis if the application cache is Redis (or a `redis`
component is configured), else in the application cache. On a cluster, use Redis: with other
caches every node counts on its own, and under `DummyCache` nothing is limited. Give the Redis
connection a short `connectionTimeout`:

```php
'components' => [
    'redis' => [
        'class' => \yii\redis\Connection::class,
        'hostname' => 'redis',
        'connectionTimeout' => 1,
    ],
],
```

If Redis cannot be reached, requests are not refused, but each attempt waits for the timeout —
by default PHP's `default_socket_timeout` of 60 seconds. After a failure, rate limiting pauses
for 60 seconds, and the failure is logged once (category `rateLimit`).

**Behind a reverse proxy or load balancer,** configure `trustedHosts` of the request component
in `config/web.php` (see `config/web.example.php`). Otherwise every guest seems to come from the
proxy, and all guests share one budget.

**When a limit is reached,** the request is refused with `429 Too Many Requests`, a
`Retry-After` header (seconds) and a message ("Too many requests, try again in 42 seconds.").
In the web interface, users see this message once at the bottom of the window; nothing is
retried automatically. Every limited response also carries `RateLimit-Policy` and `RateLimit`
headers with the remaining requests. The first refusal per grant or user and window is logged
as a warning (category `rateLimit`).

## Troubleshooting

- **The app cannot sign in:** check *Allow the HumHub app to sign in*. A white-label app needs
  its app ID under *Additional app IDs* — an unknown app ID shows an error page in the browser
  ("Client authentication failed"). The app discovers the server through
  `<base URL>/.well-known/oauth-authorization-server`; check that the web server does not block
  `/.well-known/`. An installation without it is treated as older than 1.20.
- **A client cannot be authorized:** check *Allow clients created by administrators*, that the
  client is *Active*, and that its redirect URI is listed exactly (scheme, host, path; only
  loopback URIs may differ in the port). `invalid_scope` means the client requested no scope it
  is allowed.
- **API requests answer `401`:** the access token expired (after an hour; the client refreshes
  it) or was signed with a rotated key; the grant was revoked, expired or its kind was switched
  off; the user is disabled; or the personal access token expired or was mistyped.
- **API requests answer `403` with `insufficient_scope`:** the grant lacks the scope named in
  the response — the client or the token scopes do not allow it (any more), or the token was
  created without it. Endpoints without a scope of their own need *Full access*. A `403` without it is a
  missing permission of the user, as in the web interface.
- **API requests answer `429`:** a [rate limit](#rate-limiting) was reached; wait for
  `Retry-After`, or raise the policy for integrations that legitimately need more.
- **Users cannot create tokens:** *Allow personal access tokens* is off, no group of theirs has
  *Create personal access tokens*, or they reached the maximum per user.

Details for developers: [OAuth and personal access tokens](../develop/concept-oauth.md),
[Rate limiting](../develop/concept-rate-limiting.md).
