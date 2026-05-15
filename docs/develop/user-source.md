# UserSource Architecture

> Provisioning model for HumHub 1.19+. Separates *who owns the user* from *how the user authenticates*.

## Why

Before 1.19 the AuthClient system had to do everything: authenticate the user, decide which attributes are managed, decide whether the user can be deleted, decide whether profile fields are editable. The result was god-objects (`LdapAuth` carried 10+ unrelated concerns) and integration friction — SCIM is pure provisioning with no auth at all and never fit the AuthClient mold.

## Two orthogonal concepts

| Concept | Question | Examples |
|---|---|---|
| **AuthClient** | How does the user prove who they are? | `Password`, `LdapAuth`, vanilla `\yii\authclient\OAuth2` for GitHub, SAML response, JWT token |
| **UserSource** | Who created this user, who manages their data, what is their lifecycle? | `LocalUserSource`, `LdapUserSource`, `ScimUserSource` (future) |

A user has exactly **one UserSource** (`user.user_source`) and **one or more AuthClients** (joined via the `user_auth` table).

## Design principles

1. **AuthClients stay vanilla.** A drop-in `\yii\authclient\OAuth2` works without implementing any HumHub-specific interface.
2. **UserSource is the lifecycle owner.** Creation, update, delete, attribute lock — everything goes through it.
3. **The UserSource declares which AuthClients it accepts** via `$allowedAuthClientIds`. AuthClients don't know UserSources exist.
4. **A user's UserSource is immutable after creation.** If you need to "move" a user from one source to another, that's an explicit migration, not an automatic side-effect of login.

## The `$allowedAuthClientIds` list

This single list on every UserSource governs **three things**:

1. **Login authorisation** — existing user with `user_source = X` may only log in via auth clients listed by source `X`'s allow list. Empty list = all clients allowed.
2. **Attribute sync gate** — only listed auth clients may push attributes into the user via `updateUser()`. Empty list = no sync gating, all login-allowed clients can sync.
3. **`createUser` dispatch** — when an unknown user logs in via auth client `Y`, the UserSource collection is iterated; the first source with a non-empty list containing `Y` claims the user. If no source claims it, `LocalUserSource` is the implicit fallback.

## Examples

### Default install

```php
// no userSourceCollection config needed
```

- `LocalUserSource` registers itself with `$allowedAuthClientIds = []` (empty → catch-all).
- Login via `Password` works.
- Vanilla `\yii\authclient\OAuth2` for GitHub registered in `authClientCollection` works — first login creates the user in `LocalUserSource` with `user_source = 'local'`. No attribute sync is configured, so HumHub uses what GitHub provided at registration and never overwrites it on later logins.

### SAML SSO with attribute sync into local users

```php
'userSourceCollection' => [
    'userSources' => [
        'local' => [
            'class' => LocalUserSource::class,
            'allowedAuthClientIds' => ['local', 'saml'],
            'managedAttributes' => ['email', 'firstname', 'lastname'],
        ],
    ],
],
'authClientCollection' => [
    'clients' => [
        'saml' => [...vanilla yii2 SAML client config...],
    ],
],
```

What happens:
- A user logs in via `saml` for the first time. `LocalUserSource` claims them (because `'saml'` is in its allow list). They get `user_source = 'local'`.
- On every subsequent SAML login, `BaseUserSource::updateUser()` writes the listed `managedAttributes` from the SAML assertion into `User` and `Profile`. Other fields stay user-editable.
- The same user could also log in via Password — also allowed, also doesn't sync (because Password provides no attributes).

### Form-based self-registration with approval, but SAML bypasses it

```php
'userSourceCollection' => [
    'userSources' => [
        'local' => [
            'class' => LocalUserSource::class,
            'allowedAuthClientIds' => ['local', 'saml'],
            'approval' => true,                  // form-based self-reg → admin must approve
            'trustedAuthClientIds' => ['saml'],  // SAML login → no approval
            'managedAttributes' => ['email', 'firstname', 'lastname'],
        ],
    ],
],
```

Approval is a per-request decision made by the UserSource. `requiresApproval(?string $authClientId)` is called with `null` for the form flow and with the client ID for the auth-client-driven flow.

### LDAP (reference implementation)

The LDAP module is the canonical pattern for the AuthClient/UserSource split.
Connection parameters are owned by a registry; the AuthClient and UserSource
each reference their connection by ID.

```
ldap/
  connection/
    LdapConnectionConfig.php     # value object: hostname, port, baseDn, ...
    LdapConnectionRegistry.php   # Yii component: id → config, id → LdapService
  services/LdapService.php       # constructed from LdapConnectionConfig
  authclient/LdapAuth.php        # vanilla BaseFormClient — only $connectionId
  source/LdapUserSource.php      # extends BaseUserSource — only $connectionId
```

```php
// Default install — single connection, configured via admin UI (LdapSettings)
//   id 'ldap' → LdapAuth + LdapUserSource registered automatically

// Multi-connection (config-only, no UI):
'modules' => [
    'ldap' => [
        'connections' => [
            'partner_corp' => [
                'title' => 'Partner Corp',
                'hostname' => 'ldap.partner.example',
                'port' => 636,
                'useSsl' => true,
                'baseDn' => 'ou=people,dc=partner,dc=example',
                'bindUsername' => 'cn=svc,dc=partner,dc=example',
                'bindPassword' => '...',
                'userFilter' => '(objectClass=person)',
                'usernameAttribute' => 'sAMAccountName',
                'emailAttribute' => 'mail',
                'idAttribute' => 'objectguid',
                'autoRefreshUsers' => true,
            ],
        ],
    ],
],
```

What happens:
- The Login form iterates over every registered AuthClient — so adding `partner_corp` automatically extends username/password login to that directory.
- LDAP-managed attributes (those configured per `ProfileField::ldap_attribute` + `LdapConnectionConfig::syncUserTableAttributes`) are locked in the profile UI.
- Login via Password is blocked unless the admin extended the UserSource's `allowedAuthClientIds` (defaults to just the same connection ID).
- Each connection has its own `user_source` (e.g. `user.user_source = 'partner_corp'`) and matching `user_auth.source` rows.

#### Identity resolution & self-healing

`LdapUserSource::findUser(array $attributes): ?User` is the LDAP-specific identity-resolution entry point — called from both `LdapAuth::authenticate()` (live login) and `LdapUserSource::syncUsers()` (background sync). It does more than the generic `AuthClientService::getUser()`:

1. **Primary**: look up `user_auth` by `source` + `source_id` (the configured `idAttribute` value, e.g. AD's `objectGuid`).
2. **Fallback**: when the primary misses, match by `user.user_source` + any of the normalised attributes — `email`, `username` (mapped from the connection's `usernameAttribute`), or `user.guid` (legacy: pre-1.19 installs stored the LDAP unique id there before `user_auth` existed).
3. **Self-heal**: when the fallback hits *and* a new LDAP unique id is available, the `user_auth.source_id` is rewritten transparently. A `Yii::warning` is logged with both the old and new id and the matched user.

Why this matters: when an LDAP entry is deleted and re-created (e.g. employee leaves and rejoins, AD migration changes `objectGuid`, …), the user's HumHub email/username typically survives but the LDAP unique id changes. Without the self-heal, the next login or sync would treat the user as new — and the `email` unique constraint would block re-provisioning, leaving a “ghost” HumHub user that admins had to manually unstick. With the self-heal, the `user_auth` row gets re-pointed at the new id and the next login proceeds cleanly.

Connection pairing is also robust: `LdapUserSource` finds its `LdapAuth` (and vice versa) by `connectionId`, not by relying on the convention that auth-client-id equals source-id. Configs that override either side independently work without surprises.

### SCIM (future module, no AuthClient involved)

```php
'userSourceCollection' => [
    'userSources' => [
        'scim_workday' => [
            'class' => ScimUserSource::class,
            'managedAttributes' => ['email', 'firstname', 'lastname', 'department'],
            // allowedAuthClientIds left empty — SCIM pushes attributes via REST,
            // not via login. Users still log in via whatever auth clients are
            // configured for their session (e.g. SAML), which goes through the
            // user's existing source's sync rules.
        ],
    ],
],
```

A SCIM controller endpoint receives a PATCH request and calls:

```php
Yii::$app->userSourceCollection
    ->getUserSource('scim_workday')
    ->updateUser($user, $attributesFromRequest);
```

No AuthClient is involved. Login authentication is decoupled.

## Implementing a UserSource

Most cases are covered by `BaseUserSource`. Override only what you need.

```php
class MyUserSource extends BaseUserSource
{
    public array $allowedAuthClientIds = ['my-saml'];
    public array $managedAttributes = ['email', 'firstname', 'lastname'];
    public string $usernameStrategy = UserSourceInterface::USERNAME_AUTO_GENERATE;

    public function getId(): string
    {
        return 'my-source';
    }

    public function createUser(array $attributes): ?User
    {
        // Build user, set user_source, save profile, return user — or null on failure.
    }

    // updateUser() inherited from BaseUserSource — writes $managedAttributes
    // present in $attributes onto User and Profile.
}
```

For config-only sources without custom code, use `GenericUserSource` and configure entirely via `userSourceCollection`.

## Lifecycle events

All three events are class-level on `UserSourceService` — listen via `Event::on(UserSourceService::class, ...)`.

| Event | Constant | Fires when |
|---|---|---|
| `EVENT_AFTER_CREATE` | `'afterUserSourceCreate'` | A UserSource has just created a new user. Fires exactly once per creation, regardless of path: UI self-registration, AuthClient-driven auto-registration, scheduled sync (LDAP/SCIM), admin scripts. |
| `EVENT_AFTER_UPDATE` | `'afterUserSourceUpdate'` | A UserSource sync wrote attributes via `UserSourceService::updateUser()`. **Note:** does *not* fire for ordinary user-self-edits via the profile UI — those go through `User::EVENT_AFTER_UPDATE` and `Profile::EVENT_AFTER_UPDATE`. The UserSource event is specifically about sync from the source. |
| `EVENT_AFTER_DELETE` | `'afterUserSourceDelete'` | A UserSource has removed a user via `UserSourceService::deleteUser()`. The default behaviour in `BaseUserSource` is soft-disable; concrete sources may anonymize or hard-delete. |

The legacy `Registration::EVENT_AFTER_REGISTRATION` still exists and fires only for users created through the form flow — it's the right hook for things like "show legal acceptance dialog after sign-up" but **not** for sync-time hooks.

## Database

| Column | Type | Meaning |
|---|---|---|
| `user.user_source` | `string(50)` | UserSource ID. Set on insert, immutable thereafter. |
| `user_auth.source` | `string` | AuthClient ID for non-source-owning auth clients (OAuth, SAML, etc.). |
| `user_auth.source_id` | `string` | External identifier from that AuthClient. |

Source-owning auth clients (e.g. `Password` and `LdapAuth`) do not write `user_auth` rows — they're matched via `user.user_source` directly.

## Migrating from pre-1.19 modules

| Old API | New API |
|---|---|
| `extends BaseClient` (humhub) | `extends \yii\authclient\BaseClient` (or `BaseFormClient`) |
| `implements ApprovalBypass` | Set `$trustedAuthClientIds` on the responsible UserSource — or leave `$approval = false` (default). Interface kept as empty marker only — core no longer reads it. |
| `implements SyncAttributes` + `getSyncAttributes()` | Configure `LocalUserSource::$allowedAuthClientIds` and `$managedAttributes` (or ship a dedicated `UserSource`) |
| `implements PrimaryClient` | Register a `UserSource` whose ID matches the AuthClient ID |
| `BaseClient::EVENT_CREATE_USER` | `Event::on(UserSourceService::class, UserSourceService::EVENT_AFTER_CREATE, ...)` |
| `BaseClient::EVENT_UPDATE_USER` | `Event::on(UserSourceService::class, UserSourceService::EVENT_AFTER_UPDATE, ...)` |
| `AuthClientUserService::canChangeEmail()` etc. | `UserSourceService::getForUser($user)->canChangeEmail()` etc. |
| `user.auth_mode` / `user.authclient_id` columns | `user.user_source` (string ID) |

See the `Unreleased` section of [module-migrate.md](module-migrate.md) for the complete list of removed/renamed symbols.
