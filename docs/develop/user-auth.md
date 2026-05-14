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
| **Form-based** | User submits the login form | `Login::afterValidate()` iterates `PasswordAuth` clients, calls `$client->authenticate($u, $p)` | `PasswordAuth::authenticate(string, string): ?User` |
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
    public function authenticate(string $username, string $password): ?User;
}
```

`CustomAuth` is for protocols that don't fit the OAuth/OpenID mold — SAML, JWT, Passkey/WebAuthn (when implemented). `handleAuthRequest()` runs the protocol:

- Return a `Response` to short-circuit the flow (e.g. a `Redirect` SP → IdP, or rendering an intermediate JS page).
- Return `null` once user attributes are set on the client — `AuthAction::authSuccess()` is then invoked automatically.

`PasswordAuth` is for clients that take credentials from the login form. Implementations validate the credentials against their backend (local hash, LDAP bind, …) and return the matching `User` on success. The legacy stateful pattern (set `$client->login = $form`, then call `$client->auth()` returning bool) is gone — the contract is now explicit.

## AuthAction dispatch

`humhub\modules\user\authclient\AuthAction` extends Yii's dispatcher with one priority before falling through to the OAuth/OpenID detection:

```
1. CustomAuth                → $client->handleAuthRequest()
2. OAuth1 / OAuth2 / OpenId  → Yii parent::auth()
```

Clients that don't implement `CustomAuth` and aren't OAuth/OpenID will throw a `NotSupportedException` from Yii's parent dispatcher — that's the signal to migrate to `CustomAuth`.

## Form-login path

`Login::afterValidate()` iterates the AuthClient collection, calls `$client->authenticate($this->username, $this->password)` on every `PasswordAuth` client until one returns a non-null `User`. `BaseFormClient` carries the comfort infrastructure (`$login` property for failed-attempt tracking helpers, `getUserByLogin()`, `countFailedLoginAttempts()`); the `Login` form still sets `$client->login = $this` for those helpers, but the credentials are passed explicitly to `authenticate()`.

## Identity resolution after authentication

Two different lookup mechanisms — they serve different moments in the flow.

**During `authenticate()` / `handleAuthRequest()`** the auth client knows the credentials directly. `Password` queries by username, validates the hash, returns the `User`. `LdapAuth` does the bind, fetches the entry, then delegates to `LdapUserSource::findUser($attributes)` (identity resolution lives on the source — see [`user-source.md`](user-source.md)).

**After session rehydration** (OAuth/SAML callbacks land on a second request, the client was serialised in between) the user attributes are present but the `User` instance isn't. `AuthClientService::getUser()` provides the generic post-attributes lookup: primary via `user_auth` (source + source_id), with a fallback to `user.user_source` for source-owning clients (Password/LDAP).

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
use humhub\modules\user\models\User;

class MyPasswordClient extends BaseFormClient
{
    public function getId() { return 'mypwauth'; }

    public function authenticate(string $username, string $password): ?User
    {
        // 1. Validate credentials against your backend.
        // 2. On success, populate user attributes so post-rehydration
        //    lookups still work:
        //        $this->setUserAttributes(['id' => $user->id]);
        // 3. Optionally call $this->countFailedLoginAttempts() on failure
        //    to engage the BaseFormClient rate-limit machinery.
        // 4. Return the User on success, null on failure.
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

- The AuthClient answers *“is this person who they claim to be?”* — it returns user attributes and (for source-owning clients) the matching `User`.
- The UserSource answers *“who manages this account, what attributes are theirs to own, what's the lifecycle?”* — it owns `createUser()`, `updateUser()`, approval policy, attribute lock-down, etc.

After authentication succeeds, `AuthClientService::createUser()` (for new users) or `UserSourceService::updateUser()` (for sync-time attribute pushes) hands off to the right UserSource. See [`user-source.md`](user-source.md) for the provisioning side.

## Migration from pre-1.19

| Old pattern | New pattern |
|---|---|
| `extends humhub\modules\user\authclient\BaseClient` | `extends \yii\authclient\BaseClient` (HumHub's `BaseClient` was removed) |
| `implements StandaloneAuthClient` + `authAction($authAction)` | `implements CustomAuth` + `handleAuthRequest(): ?Response` — `StandaloneAuthClient` is gone, no fallback. |
| `$authAction->controller->enableCsrfValidation = false` | `Yii::$app->controller->enableCsrfValidation = false` |
| `return $authAction->authSuccess($this)` | `return null` (AuthAction does it automatically) |
| `BaseFormClient::auth()` returning `bool`, with `$client->login` set as side input | `BaseFormClient::authenticate(string $username, string $password): ?User` — credentials passed in, User returned |
| `implements ApprovalBypass` | Configure the responsible UserSource: `'approval' => true, 'trustedAuthClientIds' => ['<client-id>']` (or `$approval = false` to skip approval entirely). Interface deprecated but still works as a fallback marker. |
| `implements SyncAttributes` + `getSyncAttributes()` | Configure `LocalUserSource::$allowedAuthClientIds` and `$managedAttributes`, or ship a dedicated UserSource. Interface deprecated. |
| `BaseClient::beforeSerialize()` (built-in hook) | No longer needed — AuthClients aren't stored in the session. See *Crossing the request boundary* above. |
| `BaseClient::EVENT_CREATE_USER` | `Event::on(UserSourceService::class, UserSourceService::EVENT_AFTER_CREATE, …)` |
| `BaseClient::EVENT_UPDATE_USER` | `Event::on(UserSourceService::class, UserSourceService::EVENT_AFTER_UPDATE, …)` |
| `humhub\modules\user\authclient\AuthAction` (subclass) | Still exists, brought back with the dispatch logic. `AuthController::actions()['external']` points at it. |

See `MIGRATE-DEV.md` for the complete list of removed/renamed symbols across 1.19.
