# Authentication

> How HumHub authenticates a user, regardless of which protocol the credentials live behind. The other half of the story — *who owns the user account* — lives in [`user-source.md`](user-source.md).

## The three families

HumHub speaks three structurally different authentication protocols. Each family is dispatched differently, so its place in the class hierarchy matters.

```
\yii\authclient\BaseClient                      ← attribute holder, normaliser
 ├── BaseOAuth → OAuth1 / OAuth2                ← Yii-native, AuthAction dispatch
 ├── OpenId                                     ← Yii-native, AuthAction dispatch
 │
 ├── BaseFormClient        implements PasswordAuth ← HumHub, login-form dispatch
 │    ├── Password
 │    └── LdapAuth
 │
 └── SAML / JWT / …      implements CustomAuth   ← HumHub, /external dispatch
```

| Family | Trigger | Dispatch path | Contract |
|---|---|---|---|
| **OAuth / OpenID** | User clicks an SSO button | `\yii\authclient\AuthAction::auth()` recognises `OAuth1`/`OAuth2`/`OpenId` and runs the protocol | Yii's `BaseOAuth` machinery |
| **Form-based** | User submits the login form | `Login::afterValidate()` iterates `PasswordAuth` clients, calls `$client->authenticate($u, $p)` | `PasswordAuth::authenticate(string, string): bool` |
| **Custom-protocol** | User lands on `/user/auth/external?authclient=<id>` | HumHub's `AuthAction::auth()` recognises `CustomAuth` and calls `$client->handleAuthRequest()` | `CustomAuth::handleAuthRequest(): ?Response` |

## The two HumHub interfaces

```php
// humhub\modules\user\authclient\interfaces\CustomAuth
interface CustomAuth
{
    public function handleAuthRequest(): ?Response;
}

// humhub\modules\user\authclient\interfaces\PasswordAuth
interface PasswordAuth
{
    public function authenticate(string $username, string $password): bool;
}
```

`CustomAuth` is for protocols that don't fit the OAuth/OpenID mold — SAML, JWT, Passkey/WebAuthn (when implemented). `handleAuthRequest()` runs the protocol:

- Return a `Response` to short-circuit the flow (e.g. a `Redirect` SP → IdP, or rendering an intermediate JS page).
- Return `null` once user attributes are set on the client — `AuthAction::authSuccess()` is then invoked automatically.

`PasswordAuth` is for clients that take credentials from the login form. Implementations validate the credentials against their backend (local hash, LDAP bind, …) and return `true` on success. They MUST also populate user attributes via `setUserAttributes()` so the downstream lookup in `AuthClientService::getUser()` has something to work with. The legacy stateful pattern (set `$client->login = $form`, then call `$client->auth()`) is gone — credentials are passed explicitly.

## AuthAction dispatch

`humhub\modules\user\authclient\AuthAction` extends Yii's dispatcher with one priority before falling through to the OAuth/OpenID detection:

```
1. CustomAuth                → $client->handleAuthRequest()
2. OAuth1 / OAuth2 / OpenId  → Yii parent::auth()
```

Clients that don't implement `CustomAuth` and aren't OAuth/OpenID will throw a `NotSupportedException` from Yii's parent dispatcher — that's the signal to migrate to `CustomAuth`.

## Form-login path

`Login::afterValidate()` iterates the AuthClient collection, calls `$client->authenticate($this->username, $this->password)` on every `PasswordAuth` client until one returns `true`. `BaseFormClient` carries the comfort infrastructure (`$login` property for failed-attempt tracking helpers, `getUserByLogin()`, `countFailedLoginAttempts()`); the `Login` form still sets `$client->login = $this` for those helpers, but the credentials are passed explicitly to `authenticate()`.

## Identity resolution after authentication

Two different lookup mechanisms — they serve different moments in the flow.

**`authenticate()` and `handleAuthRequest()` don't resolve the `User`** — they validate credentials and populate user attributes. `Password` validates the local hash and writes `['id' => $user->id]`. `LdapAuth` does the bind and writes the normalised directory entry. `CustomAuth` clients populate attributes from the protocol's response. None of them touch the `User` instance.

**Lookup happens after** in `AuthClientService::getUser()`: primary via `user_auth` (source + source_id), with a fallback to `user.user_source` for source-owning clients (Password/LDAP). For LDAP the controller's `register()` path then delegates auto-creation and the self-healing fallback (email/guid/username, `source_id` rewrite) to `LdapUserSource::findUser()` and `createUser()` — see [`user-source.md`](user-source.md).

## Single Logout (`SingleLogout`)

When the user clicks Logout in HumHub, federated identities (SAML, OIDC, …) often want to terminate the user's session at the identity provider too — Single Logout. AuthClients that support it opt in via the `SingleLogout` interface:

```php
interface SingleLogout
{
    public function singleLogout(): ?Response;
}
```

`AuthController::actionLogout()` calls `singleLogout()` on the user's current AuthClient (per `User::getCurrentAuthClient()`) before tearing down the local session. The return value drives the flow:

- **Response** (typically a redirect SP → IdP for the SLO handshake) — the local logout is paused. The IdP processes the logout and redirects back to a module-owned callback URL (e.g. `/saml-sso/logout`) that finalises the local logout via `Yii::$app->user->logout()`.
- **null** — no remote action needed (e.g. the SLO endpoint isn't configured); the local logout proceeds.

IdP-initiated SLO (the IdP sends an unsolicited logout request to the SP, no preceding click in HumHub) lives entirely in the responsible module — it owns the protocol-specific endpoint, validates the incoming request, and terminates the local session.

## Crossing the request boundary (`PendingAuthService`)

When an SSO/CustomAuth flow finishes but the user doesn't have a HumHub account yet, the registration form runs in a *second* request. That handoff used to stash the AuthClient instance in the session, which forced every client carrying non-serialisable state (connections, closures in normalize maps) to implement a `beforeSerialize()` hook.

Since 1.19 the AuthClient instance never enters the session. Instead, `humhub\modules\user\services\PendingAuthService` captures a small DTO — the client id and the already-normalised user attributes — and reconstructs the client from the AuthClientCollection on the receiving side:

```php
// AuthController (sending side)
(new PendingAuthService())->store($authClient);
return $this->redirect(['/user/registration']);

// RegistrationController (receiving side)
$pendingAuth = new PendingAuthService();
if ($pendingAuth->hasPending()) {
    $authClient = $pendingAuth->restore();  // fresh client + attributes set
    // … build registration form from $authClient->getUserAttributes() …
}

// After registration is complete:
$pendingAuth->clear();
```

Effect for custom AuthClients: nothing — they no longer need to know about session storage. Non-serialisable state stays out of the session by construction. The pre-1.19 `SerializableAuthClient` interface was removed.

## Implementing a new AuthClient

### Form-based (extends BaseFormClient)

```php
use humhub\modules\user\authclient\BaseFormClient;

class MyPasswordClient extends BaseFormClient
{
    public function getId() { return 'mypwauth'; }

    public function authenticate(string $username, string $password): bool
    {
        // 1. Validate credentials against your backend.
        // 2. On success, populate user attributes so the downstream lookup
        //    in AuthClientService::getUser() finds the User:
        //        $this->setUserAttributes(['id' => $user->id]);
        // 3. Optionally call $this->countFailedLoginAttempts() on failure
        //    to engage the BaseFormClient rate-limit machinery.
        // 4. Return true on success, false on failure.
    }
}
```

The login form picks it up automatically via the `PasswordAuth` marker once it's in the AuthClient collection. No additional wiring.

### Custom-protocol (extends Yii BaseClient)

```php
use humhub\modules\user\authclient\interfaces\CustomAuth;
use yii\authclient\BaseClient;
use yii\web\Response;
use Yii;

class MyProtocolClient extends BaseClient implements CustomAuth
{
    public function getId() { return 'myproto'; }

    protected function initUserAttributes() { return []; }

    public function handleAuthRequest(): ?Response
    {
        // First leg — initiate: build provider request, redirect.
        if (!Yii::$app->request->get('handleCallback')) {
            // ... build state, redirect to provider ...
            return Yii::$app->getResponse()->redirect($providerUrl);
        }

        // Second leg — provider callback: validate, populate attributes, return null.
        // AuthAction calls authSuccess($this) automatically.
        $this->setUserAttributes($attributesFromProvider);
        return null;
    }
}
```

Trigger via `/user/auth/external?authclient=myproto`. HumHub's `AuthAction` recognises `CustomAuth` and calls `handleAuthRequest()`.

## Relationship to UserSource

AuthClient and UserSource are orthogonal:

- The AuthClient answers *“is this person who they claim to be?”* — it validates credentials and populates the user attributes.
- The UserSource answers *“who manages this account, what attributes are theirs to own, what's the lifecycle?”* — it owns `createUser()`, `updateUser()`, approval policy, attribute lock-down, etc.

After authentication succeeds, `AuthClientService::createUser()` (for new users) or `UserSourceService::updateUser()` (for sync-time attribute pushes) hands off to the right UserSource. See [`user-source.md`](user-source.md) for the provisioning side.

## Migration from pre-1.19

| Old pattern | New pattern |
|---|---|
| `extends humhub\modules\user\authclient\BaseClient` | `extends \yii\authclient\BaseClient` (HumHub's `BaseClient` was removed) |
| `implements StandaloneAuthClient` + `authAction($authAction)` | `implements CustomAuth` + `handleAuthRequest(): ?Response` — `StandaloneAuthClient` is gone, no fallback. |
| `$authAction->controller->enableCsrfValidation = false` | `Yii::$app->controller->enableCsrfValidation = false` |
| `return $authAction->authSuccess($this)` | `return null` (AuthAction does it automatically) |
| `BaseFormClient::auth()` returning `bool`, with `$client->login` set as side input | `BaseFormClient::authenticate(string $username, string $password): bool` — credentials passed in explicitly; user lookup happens downstream in `AuthClientService::getUser()` |
| `implements ApprovalBypass` | Configure the responsible UserSource: `'approval' => true, 'trustedAuthClientIds' => ['<client-id>']` (or `$approval = false` to skip approval entirely). Interface kept as an empty marker so existing modules don't fatal-error — core no longer reads it. |
| `implements SyncAttributes` + `getSyncAttributes()` | Configure `LocalUserSource::$allowedAuthClientIds` and `$managedAttributes`, or ship a dedicated UserSource. Interface kept as empty marker only. |
| `BaseClient::beforeSerialize()` (built-in hook) | No longer needed — AuthClients aren't stored in the session. See *Crossing the request boundary* above. |
| `BaseClient::EVENT_CREATE_USER` | `Event::on(UserSourceService::class, UserSourceService::EVENT_AFTER_CREATE, …)` |
| `BaseClient::EVENT_UPDATE_USER` | `Event::on(UserSourceService::class, UserSourceService::EVENT_AFTER_UPDATE, …)` |
| `humhub\modules\user\authclient\AuthAction` (subclass) | Still exists, brought back with the dispatch logic. `AuthController::actions()['external']` points at it. |

See `MIGRATE-DEV.md` for the complete list of removed/renamed symbols across 1.19.
