# OAuth and personal access tokens

**Since 1.20.** Core ships an OAuth 2.0 authorization server and personal access tokens
(PATs) in the core module `oauth` (`humhub\modules\oauth\`). Both issue credentials for the
[HTTP API](concept-api.md); no additional module is required.

Clients:

- the **HumHub app** — a built-in system client (`client_id` `humhub-app`). The app signs in
  through the system browser and turns its token into a web session for its WebViews;
- **admin clients** — integrations and agents an administrator registers;
- **user clients** — clients a user registers for their own account, if permitted.

A **personal access token** is a static token a user creates for scripts and agents that do
not speak OAuth.

Out of scope: the client credentials grant, dynamic client registration and the device
authorization grant.

## Protocol

OAuth 2.0 as profiled by RFC 9700 (security BCP) and RFC 8252 (native apps), implemented with
[`league/oauth2-server`](https://oauth2.thephpleague.com/) ^9.4:

- **Authorization code grant with PKCE.** `S256` only. PKCE is mandatory for public clients
  (league's default) and accepted from confidential ones.
- **Refresh token grant** with rotation: every refresh issues a new refresh token and revokes
  the old one.
- No implicit grant and no password grant.

Client types:

- **Public** clients (the app, native and CLI agents) have no secret.
- **Confidential** clients authenticate at the token endpoint with `client_secret_basic` or
  `client_secret_post`.

Redirect URIs are matched exactly. Loopback URIs (`http://127.0.0.1/...`, `http://[::1]/...`)
match on any port (RFC 8252 §7.3, league's `RedirectUriValidator`).

Redirect URIs a client may register:

- `https://` URIs;
- loopback URIs;
- private-use schemes in reverse-domain form (`com.example.app:/callback`) for public clients.

Plain `http://` URIs to other hosts are rejected.

### Endpoints

| Endpoint | Purpose |
|---|---|
| `GET /oauth/authorize` | authorization request; login, gates, consent |
| `POST /oauth/authorize` | consent decision (CSRF-protected form post) |
| `POST /oauth/token` | code exchange and refresh (league) |
| `POST /oauth/revoke` | token revocation (RFC 7009) |
| `GET /.well-known/oauth-authorization-server` | authorization server metadata (RFC 8414) |
| `POST /api/v2/oauth/web-session` | app only: one-time code for a WebView session |
| `GET /oauth/web-session` | app only: redeems that code and starts the web session |

All URLs are relative to the installation's base URL. A subdirectory installation serves the
metadata below its base path (`/humhub/.well-known/oauth-authorization-server`).

**Metadata** (`issuer` = base URL; every endpoint URL uses the same scheme and host):

- the endpoint URLs (`authorization_endpoint`, `token_endpoint`, `revocation_endpoint`);
- `response_types_supported: ["code"]`;
- `grant_types_supported: ["authorization_code", "refresh_token"]`;
- `code_challenge_methods_supported: ["S256"]`;
- `token_endpoint_auth_methods_supported: ["none", "client_secret_basic", "client_secret_post"]`;
- `revocation_endpoint_auth_methods_supported`: the same three methods;
- `scopes_supported`: every registered scope.

**`/oauth/token` and `/oauth/revoke`:**

- authenticate the client only: a session or cookie a request may carry is ignored, and they are not CSRF-checked;
- are reachable for guests even when guest access is disabled;
- answer in the OAuth JSON error format.

**`/oauth/revoke`** accepts an access or refresh token, resolves its grant and deletes that
grant. Every token issued under the grant is revoked with it, as RFC 7009 permits. An unknown
or already revoked token answers `200`, as RFC 7009 requires.

### Authorization flow

`GET /oauth/authorize` is a regular web route that requires login:

1. **Login and gates.** A guest is sent to the login page; the authorization URL is kept as
   the return URL. Every login method therefore works on the way: local, LDAP, any auth client
   (Google, SAML, company SSO). All gates (2FA, legal, forced password change …) run as for
   any browser request, and the request reaches the authorize action only once they are
   closed.
2. **Validation.** league validates the request (client, redirect URI, PKCE, `state`).
   Additional checks:
   - impersonation is not active (`Yii::$app->user->impersonation->isActive()`); otherwise `403`;
   - the client is active and its type is allowed (see [Administration](#administration));
   - a user client is used by its owner only; otherwise `403`;
   - the requested scopes, reduced to what the client may get ([Scopes](#scopes)), are not empty;
     otherwise `invalid_scope`. The system client gets `full` without a `scope` parameter.
3. **Consent.** The validated `AuthorizationRequest` is stored in the session under a random
   id. The consent page posts that id back, and the id is single-use. The page shows:
   - the client name, and for a user client "created by *name*, not verified";
   - the host of the redirect URI;
   - the label of every scope.

   An admin client with *skip consent* set skips the consent page entirely; the code is
   issued right away. The system client skips the scope list and shows the confirmation
   "Sign in to the HumHub app on this device?" instead.

   Only `S256` is accepted as `code_challenge_method`.
4. **Decision.**
   - *Allow* creates the grant ([Data model](#data-model)) and completes the request with the
     grant's `guid` as league user identifier. Then 302 to the redirect URI with `code` and
     `state`.
   - *Deny* answers `access_denied`.

The league user identifier is the grant's `guid`, not the user id. It travels through the
authorization code, the refresh token and the access token (`sub`), so every token resolves
to its grant without further bookkeeping. Clients must not read `sub`; they get the user from
`GET /api/v2/account`.

A grant whose code is never exchanged has `last_used_at = NULL`. It is not listed and is
deleted by the daily cleanup after one hour.

### Lifetimes

| Item | Lifetime |
|---|---|
| authorization code | 10 minutes, single use |
| access token | 1 hour |
| refresh token | `refreshTokenLifetimeDays` (default 90), renewed on every refresh, so a grant in use stays alive |
| grant | `grantMaxLifetimeDays` (default 0 = unlimited); set at creation as `expires_at` |
| web-session code | 60 seconds, single use |

**Reuse detection.** A revoked refresh token presented again means it was copied. The grant is
deleted, so every token of it dies. A client must therefore serialize its refreshes.

## Tokens

### Access tokens

Access tokens are league JWTs, signed RS256. The access token entity overrides league's
`convertToJWT()` to add:

- `iss`: the base URL;
- `kid` in the header: `hh-` + the first 8 hex characters of the SHA-256 of the public key.

Every token's `jti` is stored ([Data model](#data-model)). League calls
`isAccessTokenRevoked()` on every validation, and the repository answers `true` (revoked)
when any of these holds:

- the row is missing or revoked;
- the grant has expired;
- the user is not enabled.

An unknown `jti` therefore never authenticates. A token forged with a stolen key does not work
without write access to the database.

### Personal access tokens

```
hhpat_<32 random base62 characters><6 base62 characters CRC32 of the random part>
```

- **Generation:** `random_bytes`.
- **Storage:** the SHA-256 hex digest of the whole token (`token_hash`) and its last four
  characters (`token_hint`, for display). The token itself is shown once, on creation, and
  never stored.
- **Lifetime:** chosen at creation from 7 days, 30 days (default), 90 days, 1 year and
  *never*. Options beyond `patMaxLifetimeDays` are hidden. *Never* is offered only when
  `patMaxLifetimeDays` is 0.
- **Creation requires re-authentication:**
  - a user with a local password (`User::getCurrentPassword()`) confirms it;
  - any other user must have logged in to the current session within the last 15 minutes
    (`user_http_session.logged_in_at`). Otherwise the form asks them to sign out and in again.

The CRC32 suffix lets `TokenAuth` reject a mistyped token without a database query, and the
`hhpat_` prefix makes leaked tokens recognizable to secret scanners.

PATs are core code; league is not involved.

### Keys

Three secrets, stored in the module settings:

| Setting | Content |
|---|---|
| `privateKey` | RSA 2048 private key, PEM (`openssl_pkey_new()`) |
| `publicKey` | the matching public key, PEM |
| `encryptionKey` | `Defuse\Crypto\Key::createNewRandomKey()->saveToAsciiSafeString()`; league encrypts authorization codes and refresh tokens with it |

**Generation.** The module's initial migration generates all three. The installer runs the
migrations, so new installations and updates share one code path. Every node of a cluster
reads the same keys from the database.

**Override.** Module properties `$privateKey`, `$publicKey` and `$encryptionKey`. When set,
they take precedence over the settings. Each accepts the value itself or a `file://` path, for
deployments that manage secrets outside the database:

```php
// config/common.php
'modules' => [
    'oauth' => [
        'privateKey' => 'file:///run/secrets/humhub-oauth-private.pem',
        'publicKey' => 'file:///run/secrets/humhub-oauth-public.pem',
        'encryptionKey' => getenv('HUMHUB_OAUTH_ENCRYPTION_KEY') ?: null,
    ],
],
```

**Rotation.** `php yii oauth/rotate-key` generates a new key pair and keeps the encryption
key.

- Access tokens signed with the old key fail validation (`kid` with prefix `hh-`, signature
  mismatch → `401`). Clients refresh.
- Refresh tokens keep working: they are encrypted with the encryption key, not signed with the
  key pair.
- PATs are not affected.

## Authentication on API requests

`humhub\modules\oauth\components\TokenAuth` (a `yii\filters\auth\AuthMethod`) authenticates
both token kinds:

1. **No token.** No `Authorization: Bearer` header → `null`.
2. **PAT.** The token starts with `hhpat_`:
   - the checksum must hold;
   - a grant of type `pat` with this `token_hash` must exist and must not be expired;
   - the user must be enabled.

   Otherwise `401`.
3. **OAuth JWT.** The token is a JWT whose header `kid` starts with `hh-`. league's
   `ResourceServer` validates it; any failure → `401`.
4. **Any other token** → `null`. The next method (e.g. the rest module's bearer or JWT auth)
   may try.

On success:

- the identity is set on the session-less user component (`$user->setIdentity()`), not
  logged in: no `last_login` write and no login events per request;
- the grant is published as `Module::$activeGrant`;
- `last_used_at` is updated when it is older than 5 minutes.

### In `/api/v2`

`humhub\components\api\BaseController`:

- **`TokenAuth` placement.** It is the first method of the chain, ahead of everything
  modules contribute via `EVENT_COLLECT_AUTH_METHODS`. It is added only when the controller
  sets `$allowSessionAuth` or declares `$scopes`.
- **New property `protected array $scopes = []`**: action id → required scope (or a list, any
  of which suffices). `'*'` is the default for actions without their own entry.
- **New behavior `scopeFilter`.** A `humhub\modules\oauth\components\ScopeFilter`, attached after
  the authenticator, with:
  - `scopes` set to `$this->scopes`;
  - `allowFull` set to `$this->allowSessionAuth`. A grant with `full` reaches exactly what the
    platform's own frontend reaches, and nothing else.

`ScopeFilter`:

- **Session and other methods.** A request not authenticated by `TokenAuth`
  (`Module::$activeGrant === null`) passes. Those authentication methods have their own
  authorization.
- **Token requests.** A token request passes when either holds:
  - `full` is among the grant's effective scopes and `allowFull` is set;
  - the action's required scope (`scopes[actionId] ?? scopes['*']`) is among them.
- **Rejection.** Otherwise `403` with `{"error": "insufficient_scope", "scope": "<required>"}`
  and `WWW-Authenticate: Bearer error="insufficient_scope", scope="<required>"`.
- **Fail-closed.** An action without a declared scope is reachable with `full` only.

A scope is an upper bound. Permissions are checked exactly as for the same user in the web
interface.

Gates: token requests are session-less, so `GateFilter` classifies them as
`RequestClass::Api` and only gates that apply to API requests intercept (forced password
change, maintenance mode). As `docs/develop/user-gates.md` states, every other gate is
enforced when the credential is issued. Here, issuance is the authorization flow or the PAT
form, both regular web requests subject to all gates.

## Scopes

A scope is a `humhub\modules\oauth\models\Scope`:

- id;
- label (translated), shown on the consent page and the token form.

`humhub\modules\oauth\components\ScopeRegistry::getScopes()` returns:

- the core scopes below;
- every scope modules append in `ScopeRegistry::EVENT_COLLECT_SCOPES` (`ScopesEvent::$scopes`).

Rules for scope ids:

- A module scope id starts with the module id and a colon (`calendar:read`).
- `full` is reserved.
- A grant may contain an id that is not registered (e.g. its module was disabled); it is
  ignored.

### Core scopes

| Scope | Label | Endpoints |
|---|---|---|
| `full` | Full access to your account | everything the web interface can do (`allowFull`) |
| `account:read` | Read your account and settings | `GET account`, `GET account/blocked-users`, `GET notification/settings` |
| `account:write` | Change your settings | `PATCH notification/settings`, `POST notification/settings/reset`, `POST notification/settings/reset-all` |
| `notifications:read` | Read your notifications | `GET notification` |
| `notifications:write` | Mark notifications as seen | `POST notification/mark-as-seen` |
| `users:read` | Read people and profiles | `GET user`, `GET user/states`, `GET user/field-values`, `GET user/tags`, `GET user/picker`, `GET user/<id>/follow`, `GET user/<id>/friendship` |
| `users:write` | Follow people and manage friendships | `PUT`/`DELETE user/<id>/follow`, `PUT`/`DELETE user/<id>/friendship` |
| `spaces:read` | Read spaces and memberships | `GET space`, `GET space/states`, `GET space/<id>/membership`, `GET space/<id>/follow` |
| `spaces:write` | Join, leave and follow spaces | `PUT`/`DELETE space/<id>/membership`, `PUT`/`DELETE space/<id>/follow` |
| `content:read` | Read content, comments and likes | `GET content/<id>/comments`, `GET comment/<id>`, `GET comment/<id>/replies`, `GET comment/<id>/permissions`, `GET content/<id>/controls`, `GET like/<id>`, `GET like/<id>/users`, `GET like/states`, `GET activity`, `GET topic/picker` |
| `content:write` | Write comments, like, upload files | `POST comment`, `PATCH`/`DELETE comment/<id>`, `PUT`/`DELETE like/<id>`, `POST file`, `DELETE file/<id>` |

These endpoints declare no scope and are reachable with `full` only:

- `POST module/<id>/enable`;
- every `marketplace/*` endpoint;
- `POST oauth/web-session`.

### Effective scopes

The scopes of a grant are fixed at creation. On every request `TokenAuth` intersects them with
what its client or token kind may hold now, so an administrator's restriction applies to
existing grants immediately:

| Grant | Effective scopes |
|---|---|
| system client | `full` |
| admin client | grant ∩ client's allowed scopes |
| user client | grant ∩ client's allowed scopes ∩ `userClientScopes` |
| PAT | grant ∩ `patScopes` |

`full` can be held by:

- the system client;
- an admin client the administrator allowed it;
- a PAT, if the administrator added it to `patScopes`.

User clients never hold `full`.

Scopes are grant-based: enforcement always uses the grant's effective scopes, never the
`scopes` claim of an access token. At code exchange and refresh, `finalizeScopes()` writes
the current effective scopes into the new token for information only; a narrower `scope`
parameter on refresh has no effect. An unregistered scope id is ignored and never fails an
exchange or refresh — a refresh token is consumed before league validates scopes, so a
failure there would burn it and the client's retry would revoke the grant.

Controllers keep the `'*'` default for the stronger scope of the controller and list
read actions explicitly, so a newly added action needs the write scope until it is
classified.

## WebView sessions (app)

The app turns its grant into a regular web session for its WebViews:

1. **Request a code.** The app calls `POST /api/v2/oauth/web-session` with its access token.
   - Only grants of the system client get a code; any other grant gets `403`.
   - The response is
     `{"url": "<base>/oauth/web-session?code=<code>", "headers": {"X-HumHub-Session-Secret": "<secret>"}, "expiresIn": 60}`
     with `Cache-Control: no-store`.
   - Code and secret are 32 random bytes each, base64url. Only
     `SHA-256(<code> + ":" + <secret>)` is stored in `oauth_web_session_code`.
   - The secret prevents login CSRF: a link to the URL alone, sent to someone else's browser,
     cannot log that browser in, because a plain link cannot set a request header.
2. **Redeem it.** The WebView loads that URL, optionally with `&returnUrl=<relative URL>`, and
   sends the returned headers with that first request. `GET /oauth/web-session`:
   - reads the secret from the `X-HumHub-Session-Secret` header; a missing header → error page;
   - deletes the code row atomically (`DELETE … WHERE code_hash = ? AND expires_at > now`); no
     deleted row (unknown code, wrong secret, expired, used) → error page. A wrong secret does
     not match the row, so it does not consume the code;
   - checks that the grant is valid and the user is enabled;
   - always starts a fresh session: a user already logged in to this WebView, even the same
     one, is logged out first. A session bound to the very grant being redeemed is unbound
     before, so this logout does not revoke it. No existing session (for example one with a
     pending second factor) is ever marked, and a regular browser session is never bound;
   - logs in the user without an auto-login cookie (`login($user, 0)`);
   - stores the grant id under the session key `oauth.grantId`;
   - sets the session key `GateManager::SESSION_KEY_ISSUED_CREDENTIAL`;
   - redirects to `returnUrl`, if it is a path on this installation (no scheme or host, no
     `//`, no backslash, no whitespace or control characters), otherwise to the dashboard, with
     `Referrer-Policy: no-referrer` and `Cache-Control: no-store`.
3. **Binding.** `Events::onBeforeAction` (on `humhub\components\Controller::EVENT_BEFORE_ACTION`)
   loads the grant of a session holding `oauth.grantId`. A deleted or expired grant logs the
   session out, and the next page is the login page.
4. **Logout.** Logging out of a session holding `oauth.grantId` deletes the grant
   (`yii\web\User::EVENT_BEFORE_LOGOUT`). A logout in the WebView is therefore an app logout.

**Gates in a WebView session.** A WebView session is a regular browser session and is
classified as usual (`RequestClass::FullPage` / `Ajax`). Every gate applies, for example
legal (new terms), forced password change, maintenance mode and the gates of other modules.

The one exception is a gate that verifies the login itself, such as a second factor. Such a
gate already ran in the system browser before the grant was issued, so it must not ask again:

- The new marker interface `humhub\components\gates\LoginVerificationGateInterface` identifies
  such gates. It has no methods.
- `GateManager::SESSION_KEY_ISSUED_CREDENTIAL` marks a session established from an issued
  credential.
- In a marked session, `GateManager::findOpenGate()` skips every gate implementing the
  interface.

A login-verification gate that does not implement the interface still intercepts the
WebView: it asks again, which is safe.

`docs/develop/user-gates.md` documents the interface and the session key.

## Data model

All timestamps are Unix integers.

**`oauth_client`**

| Column | Type | |
|---|---|---|
| `id` | int PK | |
| `client_id` | varchar(80) unique | random 32 hex characters; `humhub-app` for the system client |
| `type` | varchar(10) | `system`, `admin`, `user` |
| `name` | varchar(100) | |
| `description` | text null | |
| `owner_user_id` | int null, FK `user` cascade | user clients |
| `secret_hash` | char(64) null | SHA-256 hex; null = public client |
| `redirect_uris` | text | JSON list |
| `scopes` | text | JSON list of allowed scopes |
| `skip_consent` | tinyint(1) | admin clients only |
| `is_active` | tinyint(1) | |
| `created_at`, `created_by` | int, int null | |

**`oauth_grant`**

| Column | Type | |
|---|---|---|
| `id` | int PK | |
| `guid` | char(36) unique | league user identifier |
| `user_id` | int, FK `user` cascade | |
| `type` | varchar(10) | `oauth`, `pat` |
| `client_id` | int null, FK `oauth_client` cascade | `oauth` grants |
| `name` | varchar(100) | client name, or the PAT name the user typed |
| `scopes` | text | JSON list |
| `token_hash` | char(64) null unique | PAT |
| `token_hint` | char(4) null | PAT |
| `user_agent` | varchar(255) null | of the code exchange (app: its device) |
| `created_at`, `last_used_at`, `expires_at` | int, int null, int null | |

**`oauth_auth_code`, `oauth_access_token`, `oauth_refresh_token`**

| Column | Type | |
|---|---|---|
| `id` | varchar(80) PK | league identifier (`jti` for access tokens) |
| `grant_id` | int, FK `oauth_grant` cascade | |
| `access_token_id` | varchar(80) | `oauth_refresh_token` only |
| `expires_at` | int | |
| `is_revoked` | tinyint(1) | |

**`oauth_web_session_code`**

| Column | Type | |
|---|---|---|
| `code_hash` | char(64) PK | |
| `grant_id` | int, FK `oauth_grant` cascade | |
| `expires_at` | int | |

Rules:

- **Revoking a grant deletes the row.** The foreign keys remove its codes and tokens, which
  makes every one of them unknown and thus revoked. Grants keep no revoked state.
- **Deleting a user or a client** removes its grants the same way.
- **Soft-deleting or disabling a user** is covered by the usability check of the repositories
  (`GrantService::isUsable()`), which every token validation runs. The grants remain, but authenticate nobody.
- **The initial migration** creates the tables, the system client and the keys.

The system client row:

| Field | Value |
|---|---|
| `client_id` | `humhub-app` |
| `type` | `system` |
| `name` | `HumHub App` |
| public | yes |
| `redirect_uris` | `["com.humhub.app:/oauth/callback"]` |
| `scopes` | `["full"]` |

It cannot be edited or deleted. It can only be deactivated through `allowApp`.

**Daily cleanup** (`CronController::EVENT_ON_DAILY_RUN`) deletes:

- expired codes and tokens, and expired web-session codes;
- expired grants;
- `oauth` grants with `last_used_at IS NULL` that are older than one hour.

## User interface

### Account: "Apps & Tokens"

A new tab in the security settings, `SecuritySettingsMenu`:

- label "Apps & Tokens";
- sortOrder 350;
- route `oauth/account/index`.

The tab is visible when any of these holds:

- the user has a grant;
- the user may create PATs;
- the user may create user clients.

Its sections:

- **Devices.** Grants of the system client: device (from `user_agent`, via
  `UserAgentHelper`), created, last used, *Sign out*.
- **Connected apps.** Grants of admin and user clients: client, scopes, created, last used,
  *Disconnect*.
- **Personal access tokens.** Name, `hhpat_…<hint>`, scopes, expiry, last used, *Revoke*.
  Then *Create token* (name, scopes from `patScopes`, lifetime, re-authentication). The token
  is shown once.
- **My OAuth clients.** Only with the permission `ManageOwnOAuthClients`. Create, edit, delete,
  regenerate the secret (shown once). The scopes are chosen from `userClientScopes`.

Creating a PAT or connecting an admin or user client sends the user the notification
`GrantCreatedNotification` (web and mail). Signing in with the app does not.

### Administration: "Apps & API Access"

A new tab in `AuthenticationMenu` (Users → Settings):

- label "Apps & API Access";
- sortOrder 500;
- route `oauth/admin/index`;
- permission `ManageSettings`.

**Settings:**

| Setting | Default | |
|---|---|---|
| `allowApp` | on | the HumHub app may sign in |
| `allowClients` | off | admin clients may authorize |
| `allowUserClients` | off | user clients may authorize; creating them requires `ManageOwnOAuthClients` |
| `userClientScopes` | all `*:read` core scopes | upper bound for user clients |
| `allowPersonalAccessTokens` | off | PATs authenticate; creating them requires `CreatePersonalAccessToken` |
| `patScopes` | all `*:read` core scopes | upper bound for PATs |
| `patMaxLifetimeDays` | 365 | 0 = unlimited, offers *never* |
| `refreshTokenLifetimeDays` | 90 | |
| `grantMaxLifetimeDays` | 0 | 0 = unlimited |
| `revokeOnPasswordChange` | off | a password change deletes all grants of the user |

Each `allow…` switch also takes effect on existing grants: `TokenAuth` and `isAccessTokenRevoked()`
reject grants of a disallowed kind.

**Clients.** List, create, edit, deactivate, delete, regenerate the secret. Fields:

- name, description;
- public or confidential;
- redirect URIs;
- allowed scopes (`full` allowed);
- skip consent.

**Grants.** Every grant, filterable by user, client and type, with *Revoke* and *Revoke all*
(for the current filter).

**Group permissions** (Users → Groups → Permissions), both off by default:

- `CreatePersonalAccessToken`;
- `ManageOwnOAuthClients`.

## PSR-7 bridge

`humhub\components\psr\` is the only place that knows the PSR-7 implementation
(`guzzlehttp/psr7`, required directly by core). Other code types against
`Psr\Http\Message\*` only:

- `ServerRequestFactory::fromYii(yii\web\Request): ServerRequestInterface`. Carries method,
  URI, headers, raw body, parsed body, query, cookies and uploaded files.
- `ResponseBridge::toYii(ResponseInterface, yii\web\Response): yii\web\Response`. Carries
  status, headers and body.
- `HttpFactory::instance()` provides the PSR-17 factories. Nobody instantiates
  `GuzzleHttp\Psr7\*` directly.

## Module layout

```
protected/humhub/modules/oauth/
├── Module.php                  # key properties, $activeGrant
├── config.php                  # URL rules, events
├── Events.php                  # session binding, logout, menus, cron, password change
├── commands/OauthController.php          # rotate-key
├── components/
│   ├── AuthorizationServerFactory.php    # league AuthorizationServer / ResourceServer
│   ├── KeyStore.php                      # read, generate, rotate keys
│   ├── TokenAuth.php
│   ├── TokenResolver.php                 # own JWT detection, token → grant for revocation
│   ├── ScopeFilter.php
│   └── ScopeRegistry.php
├── controllers/
│   ├── AuthorizeController.php
│   ├── TokenController.php               # token, revoke
│   ├── MetadataController.php
│   ├── WebSessionController.php
│   ├── AccountController.php
│   ├── AdminController.php
│   └── api/WebSessionController.php
├── entities/                   # league entity implementations
├── repositories/               # league repository implementations
├── services/GrantService.php   # create, usability, effective scopes, revoke
├── events/ScopesEvent.php
├── models/                     # Client, Grant, AccessToken, RefreshToken, AuthCode,
│                               # WebSessionCode, Scope, forms
├── notifications/GrantCreatedNotification.php
├── permissions/CreatePersonalAccessToken.php
├── permissions/ManageOwnOAuthClients.php
├── migrations/
├── views/
└── tests/
```

## Changes outside the module

- `components/api/BaseController`: `$scopes`, `TokenAuth` first in `getAuthMethods()`, the
  `scopes` behavior.
- Every core v2 controller declares `$scopes` as listed in [Core scopes](#core-scopes).
- `components/gates/`:
  - `LoginVerificationGateInterface` (new);
  - `GateManager::SESSION_KEY_ISSUED_CREDENTIAL` and the corresponding skip in
    `findOpenGate()`;
  - update `docs/develop/user-gates.md` accordingly.
- `components/psr/` (new).
- `composer.json`: `league/oauth2-server` ^9.4, `guzzlehttp/psr7` ^2.13.
- Docs:
  - `docs/develop/concept-api.md` "Authentication model": core now ships `TokenAuth`, it runs
    before contributed methods, and `$scopes` is introduced;
  - `docs/develop/module-migrate-1.20.md`: new `BaseController::$scopes`, `TokenAuth`
    precedence and `ScopeRegistry::EVENT_COLLECT_SCOPES`;
  - `CHANGELOG.md`.

## rest module

The rest module makes `/api/v1` reachable with OAuth grants and PATs through one scope:

- **Scope.** It registers `rest:v1` ("Access the REST API v1") in
  `ScopeRegistry::EVENT_COLLECT_SCOPES`. The scope is not among the defaults of
  `userClientScopes` and `patScopes`; an administrator grants it explicitly.
- **Authentication.** `components/BaseController::behaviors()` prepends `TokenAuth` to its
  `authMethods` and attaches `ScopeFilter` with:
  - `scopes` = `['*' => 'rest:v1']`;
  - `allowFull` = `false`.

  `full` never reaches `/api/v1`. Controllers of other modules that extend it (calendar,
  mail …) inherit this.
- **Allowlist.** After authentication, a request authenticated by `TokenAuth` additionally
  passes `AuthMethods::isUserEnabled()`, the same allowlist as the module's own methods.
- **Version.** CHANGELOG entry under the existing `0.13.0 (Unreleased)`; `minVersion` stays
  1.20.

## twofa module

- `TwofaGate` implements `LoginVerificationGateInterface`, so the app's WebView sessions do
  not ask for the second factor again.
- CHANGELOG entry under the open `develop` section; `minVersion` 1.20.

## App integration

What the HumHub app implements against this:

1. **Discovery.** The user enters the instance URL. The app reads
   `<base>/.well-known/oauth-authorization-server`. If it is missing, the instance predates
   1.20 and the app keeps its current WebView login.
2. **Sign-in.** The app opens `authorization_endpoint` with these parameters in
   `ASWebAuthenticationSession` (iOS) or a Custom Tab (Android):
   - `client_id=humhub-app`;
   - `redirect_uri=com.humhub.app:/oauth/callback`;
   - `response_type=code`;
   - `code_challenge` (S256);
   - `state`.

   It then exchanges the code at `token_endpoint` and sends a descriptive `User-Agent` with
   the exchange (stored as the device).
3. **Storage.** The refresh token goes to the Keychain or Keystore. The access token is held
   in memory.
4. **API.** Requests carry `Authorization: Bearer <access token>`.
   - `401`: refresh once (refreshes serialized); if that fails, sign in again.
   - `403` with `gate`: open the `url` in the WebView.
5. **WebView.** `POST /api/v2/oauth/web-session`, then load the returned `url` with the
   returned `headers` as request headers (`URLRequest` on iOS, `loadUrl(url, headers)` on
   Android) — only on this first load. Whenever the WebView reaches the login page, request a
   new code.
6. **Sign-out.** `POST /oauth/revoke` with the refresh token, then clear the WebView cookies.
