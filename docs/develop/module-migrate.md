# Module Migration Guide

Migration notes for keeping a module compatible with newer HumHub core releases.

Each minor release line has its own file with the breaking changes, new APIs and deprecations. Notes for the **current development cycle** live in the `Unreleased` section below — pull requests that introduce breaking changes should add an entry here, not in a release-specific file.

## Unreleased

- Refactored `ContentAddons`
  - Improved `humhub\modules\content\components\ContentAddonActiveRecord`, now required `content_id` attribute
    - Removed `user` relation, use `createdBy` instead.
  - Removed `humhub\modules\content\components\ContentAddonController`
  - Introduced `ContentProvider` interface (May change!)
- Added `RecordMap` to improve polymorphic models relations
- Refactored `comment` module
  - Replaced Polymorphic Relations with `comment.content_id` and `comment.parent_comment_id`
  - Introduced `CommentListService`
  - Removed `CommentForm`
- Refactored `like` module
  - Introduced `LikeService` and added `like.content_id`
  - Used `RecordMap` for ContentAddon relations
- Removed methods `getContentPlainTextInfo()` and `getContentPlainTextPreview()` from the class `SocialActivity`(`BaseNotification`)
  - Replace them with `getContentInfo()` and `getContentPreview()` in all extended classes, especially inside the method `getMailSubject()`
- Replaced classes:
  - `humhub\widgets\BaseMenu` => `humhub\modules\ui\menu\widgets\Menu`
  - `humhub\widgets\Button` => `humhub\widgets\bootstrap\Button`
  - `humhub\widgets\Label` => `humhub\widgets\bootstrap\Badge`
  - `humhub\widgets\ContentTagDropDown` => `humhub\modules\content\widgets\ContentTagDropDown`
  - `humhub\widgets\DatePicker` => `humhub\modules\ui\form\widgets\DatePicker`
  - `humhub\widgets\DurationPicker` => `humhub\modules\ui\form\widgets\DurationPicker`
  - `humhub\widgets\GlobalConfirmModal` => `humhub\widgets\modal\GlobalConfirmModal`
  - `humhub\widgets\GlobalModal` => `humhub\widgets\modal\GlobalModal`
  - `humhub\widgets\Link` => `humhub\widgets\bootstrap\Link`
  - `humhub\widgets\LinkPager` => `humhub\widgets\bootstrap\LinkPager`
  - `humhub\widgets\Modal` => `humhub\widgets\modal\Modal`
  - `humhub\widgets\ModalButton` => `humhub\widgets\modal\ModalButton`
  - `humhub\widgets\ModalClose` => `humhub\widgets\modal\ModalClose`
  - `humhub\widgets\ModalDialog` => `humhub\widgets\modal\Modal`
  - `humhub\widgets\Tabs` => `humhub\widgets\bootstrap\Tabs`
  - `humhub\widgets\TimePicker` => `humhub\modules\ui\form\widgets\TimePicker`
  - `humhub\modules\search\interfaces\Searchable` => `humhub\modules\content\interfaces\Searchable`
  - `humhub\components\queue` => `humhub\modules\queue\ActiveJob`
  - `humhub\libs\Html` => `humhub\helpers\Html`
  - `humhub\libs\ThemeHelper` => `humhub\helpers\ThemeHelper`
  - `humhub\modules\activity\widgets\Stream` => `humhub\modules\activity\widgets\ActivityStreamViewer`
  - `humhub\modules\content\components\actions\ContentContainerStream` => `humhub\modules\stream\actions\ContentContainerStream`
  - `humhub\modules\content\widgets\WallEntry` => `humhub\modules\content\widgets\stream\WallStreamEntryWidget`
  - `humhub\modules\space\modules\manage\widgets\Menu` => `humhub\modules\space\widgets\HeaderControlsMenu`
  - `humhub\modules\topic\widgets\TopicLabel` => `humhub\modules\topic\widgets\TopicBadge`
  - `humhub\modules\ui\form\widgets\ActiveField` => `humhub\widgets\form\ActiveField`
  - `humhub\modules\ui\form\widgets\ActiveForm` => `humhub\widgets\form\ActiveForm`
  - `humhub\modules\ui\form\widgets\ContentHiddenCheckbox` => `humhub\widgets\form\ContentHiddenCheckbox`
  - `humhub\modules\ui\form\widgets\ContentVisibilitySelect` => `humhub\widgets\form\ContentVisibilitySelect`
  - `humhub\modules\ui\form\widgets\FormTabs` => `humhub\widgets\bootstrap\FormTabs`
  - `humhub\modules\ui\form\widgets\SortOrderField` => `humhub\widgets\form\SortOrderField`
  - `humhub\modules\ui\mail\DefaultMailStyle` => `humhub\helpers\MailStyleHelper`
  - `humhub\modules\ui\view\components\View` => `humhub\components\View`
  - `humhub\modules\ui\view\helpers` => `humhub\helpers\ThemeHelper`
  - `humhub\modules\user\components\ProfileStream` => `humhub\modules\user\actions\ProfileStreamAction`
  - `humhub\modules\user\widgets\UserPicker` => `humhub\modules\user\widgets\UserPickerField` for rendering, `humhub\modules\user\models\UserPicker` for searching
- Removed classes:
  - `humhub\widgets\BootstrapComponent`
  - `humhub\assets\Select2BootstrapAsset`
  - `humhub\modules\search\events\SearchAddEvent`
  - `humhub\libs\DynamicConfig`
  - `humhub\modules\content\widgets\LegacyWallEntryControlLink`
  - `humhub\modules\content\widgets\Stream`
  - `humhub\modules\directory\Module`
  - `humhub\modules\file\widgets\FileUploadButton`
  - `humhub\modules\file\widgets\FileUploadList`
  - `humhub\modules\queue\driver\MySQLCommand`
  - `humhub\modules\user\authclient\AuthClientHelpers`
  - `humhub\modules\user\authclient\Facebook`
  - `humhub\modules\user\authclient\GitHub`
  - `humhub\modules\user\authclient\Google`
  - `humhub\modules\user\authclient\LinkedIn`
  - `humhub\modules\user\authclient\Live`
  - `humhub\modules\user\authclient\Twitter`
  - `humhub\modules\user\authclient\ZendLdapClient`
- Replaced methods:
  - `humhub\widgets\bootstrap\Link::asLink()` => `humhub\widgets\bootstrap\Link::to()`
  - `humhub\widgets\bootstrap\Button::asLink()` => `humhub\widgets\bootstrap\Link::to()`
  - `humhub\widgets\bootstrap\ModalButton::asLink()` => `humhub\widgets\bootstrap\Link::modal()`(new since v1.19)
  - `humhub\modules\ui\menu\widgets\Menu->addItem([...])` => `humhub\modules\ui\menu\widgets\Menu->addEntry(new MenuLink([...]))`(used in module files `Events.php` as `$event->sender->addItem([...])`)
  - `humhub\widgets\bootstrap\Link::userPickerSelfSelect()` => `humhub\modules\user\widgets\UserPickerField::selfSelect()` or use new option `UserPickerField->selfSelect`
  - `humhub\modules\content\models\Content->delete()` => `humhub\modules\content\models\Content->getPolymorphicRelation()->delete()`
  - `humhub\modules\content\models\Content->hardDelete()` => `humhub\modules\content\models\Content->getPolymorphicRelation()->hardDelete()`
- Refactored `Activities`
  - Make sure Content related Activities are now extended from `BaseContentActivity`
  - `getTitle` and `getDescription` are now `static`.
  - Instead of View files you need to implement a `getMessage()` method which returns the Activity text.
  - Use following code to create a Activity `ActivityManager::dispatch(TaskCompletedActivity::class, $this->task, $user)`
- Introduced **UserSource architecture** — separates user provisioning (who owns the user) from authentication (how the user logs in)
  - New `humhub\modules\user\source\UserSourceInterface` — contract for user provisioning sources
  - New `humhub\modules\user\source\BaseUserSource` — abstract base with sensible defaults; provides a default `updateUser()` that applies attributes listed in `$managedAttributes`
  - New `humhub\modules\user\source\LocalUserSource` — handles self-registered / admin-created users; default fallback for any AuthClient not claimed by another UserSource
  - New `humhub\modules\user\source\GenericUserSource` — fully config-driven source for custom integrations
  - New `humhub\modules\user\source\UserSourceCollection` — application component (`Yii::$app->userSourceCollection`); exposes `findUserSourceForAuthClient(string $clientId)` for AuthClient → UserSource dispatch
  - New `humhub\modules\user\services\UserSourceService` — per-user capability checks and lifecycle helpers
    - `UserSourceService::getForUser(?User $user = null)` — factory method; falls back to current identity
    - `UserSourceService::updateUser(array $attributes)` — updates user via UserSource and fires lifecycle event
    - `UserSourceService::deleteUser()` — removes user via UserSource and fires lifecycle event
    - `UserSourceService::triggerAfterCreate(User $user)` — fires lifecycle event after creation
  - New `humhub\modules\ldap\source\LdapUserSource` — extracted from `LdapAuth`; handles LDAP user lifecycle
  - Database: `user.auth_mode` + `user.authclient_id` replaced by `user.user_source` (string source ID)
  - Database: LDAP identity now stored in `user_auth` table (`source='ldap'`, `source_id=<idAttribute value>`) — consistent with OAuth
  - New class-level lifecycle events on `UserSourceService` — listen via `Event::on(UserSourceService::class, ...)`:
    - `UserSourceService::EVENT_AFTER_CREATE` (`'afterUserSourceCreate'`) — fired after a UserSource creates a user
    - `UserSourceService::EVENT_AFTER_UPDATE` (`'afterUserSourceUpdate'`) — fired after a UserSource updates a user
    - `UserSourceService::EVENT_AFTER_DELETE` (`'afterUserSourceDelete'`) — fired after a UserSource removes a user
  - **AuthClient → UserSource link is declarative on the UserSource side** — AuthClients stay vanilla `\yii\authclient\*` implementations with no HumHub-specific interfaces. Each UserSource declares which AuthClient IDs it is responsible for via `$allowedAuthClientIds`; that list governs login authorisation, sync, and createUser dispatch.
  - LDAP: `LdapUserSource::$allowedAuthClientIds` (default `['ldap']`) — restricts which auth clients LDAP users may use; configurable in admin UI
  - LDAP: login with a disallowed auth client is now blocked in `AuthController` with an error flash
- Removed `humhub\modules\user\authclient\BaseClient`
  - Replace `extends BaseClient` with `extends \yii\authclient\BaseClient` (or `BaseFormClient` for form-based clients)
  - `BaseClient::EVENT_CREATE_USER` removed — replace listeners with `Event::on(UserSourceService::class, UserSourceService::EVENT_AFTER_CREATE, $handler)`
  - `BaseClient::EVENT_UPDATE_USER` removed — replace listeners with `Event::on(UserSourceService::class, UserSourceService::EVENT_AFTER_UPDATE, $handler)`
  - `BaseClient::canBypassApproval()` removed — configure on the UserSource via `$approval` / `$trustedAuthClientIds` (see below)
  - `BaseClient::beforeSerialize()` removed — implement `humhub\modules\user\authclient\interfaces\SerializableAuthClient` instead
- Removed `humhub\modules\user\authclient\interfaces\SyncAttributes` (was deprecated since 1.16; the legacy sync path in `AuthClientService` is removed)
  - Replacement: have a UserSource declare which AuthClient IDs it accepts via `$allowedAuthClientIds`. The UserSource's `updateUser()` (or the default in `BaseUserSource` driven by `$managedAttributes`) writes the synced fields.
  - Affected modules requiring migration: `saml-sso`, `jwt-sso`, `spd-login`, and any custom AuthClient that implemented `SyncAttributes`. Existing local users authenticating via SAML/JWT will no longer be sync'd unless an admin opts in by adding the auth client ID to `LocalUserSource::$allowedAuthClientIds` and listing the synced fields in `LocalUserSource::$managedAttributes`.
- Removed `humhub\modules\user\authclient\interfaces\SerializableAuthClient` and its `beforeSerialize()` hook. AuthClient instances are no longer stored in the session at all — `AuthController` now hands the auth state to the registration form via `humhub\modules\user\services\PendingAuthService`, which captures only the client id + already-normalised user attributes. The AuthClient is reconstructed from the AuthClientCollection on the receiving side. Closures in normalize maps, connection handles, and other non-serialisable client state are no longer a session concern. Modules with custom AuthClients can drop `implements SerializableAuthClient` and their `beforeSerialize()` method.
- Deprecated `humhub\modules\user\authclient\interfaces\ApprovalBypass` — approval is now configured on the UserSource side. AuthClients stay vanilla `\yii\authclient\*` implementations.
  - The interface is kept as an empty marker so modules still implementing it don't fatal-error, but core no longer reads it. `RegistrationController` and `AuthClientService::allowSelfRegistration()` no longer fall back on `instanceof ApprovalBypass`.
  - Migration: drop `implements ApprovalBypass` from your AuthClient. To skip approval for users provisioned via that auth client, configure the responsible UserSource with `'approval' => true, 'trustedAuthClientIds' => ['<client-id>']` — or leave `$approval = false` (default) to skip approval entirely for that source.
  - `UserSourceInterface::requiresApproval(?string $authClientId = null)` decides per-request: form-based self-registration passes `null`; auth-client-driven registration passes the client ID.
  - Core `LdapAuth` no longer implements `ApprovalBypass`; the LDAP approval policy is owned by `LdapUserSource`.
- Deprecated `humhub\modules\user\authclient\interfaces\PrimaryClient` is no longer read by core — `AccountController::actionConnectedAccounts()` and `AccountSettingsMenu::getSecondaryAuthProviders()` filter purely on `BaseFormClient` now (source-owning clients all extend it). `Password` no longer implements `PrimaryClient`. Interface kept as empty marker for backwards compatibility.
- Removed `humhub\modules\user\authclient\interfaces\StandaloneAuthClient` and its dispatcher fallback. Migrate to the new `humhub\modules\user\authclient\interfaces\CustomAuth` interface:
  ```php
  // Before:
  use humhub\modules\user\authclient\BaseClient;
  use humhub\modules\user\authclient\interfaces\StandaloneAuthClient;

  class MyClient extends BaseClient implements StandaloneAuthClient {
      public function authAction($authAction) {
          // custom logic
          return $authAction->authSuccess($this);
      }
  }

  // After:
  use humhub\modules\user\authclient\interfaces\CustomAuth;
  use yii\authclient\BaseClient;
  use yii\web\Response;

  class MyClient extends BaseClient implements CustomAuth {
      public function handleAuthRequest(): ?Response {
          // custom logic. Return a Response for a redirect (e.g. SP → IdP),
          // or null to signal completion — AuthAction calls authSuccess()
          // automatically.
      }
  }
  ```
  `humhub\modules\user\authclient\AuthAction` no longer falls back on the legacy marker — custom auth clients that didn't migrate will throw a `NotSupportedException` at dispatch time.
- Renamed `humhub\modules\user\authclient\BaseFormAuth` to `BaseFormClient`. The previous name doubled the "Auth" suffix with the surrounding `authclient/` namespace — the new name mirrors Yii's `BaseClient` parent. Drop-in rename; the class lives in the same namespace.
- Added `humhub\modules\user\authclient\interfaces\SingleLogout` — capability marker for AuthClients that support Single Logout (terminating the user's session at the identity provider, not just locally). `AuthController::actionLogout()` calls `$client->singleLogout(): ?Response` on the user's current AuthClient before tearing down the local session; a returned Response (typically a redirect SP → IdP) short-circuits, the IdP eventually redirects back to a module-owned callback URL that finalises the local logout. Modules previously implementing SLO via `EVENT_BEFORE_ACTION` interception on `AuthController` (saml-sso) should migrate to the interface and drop the event hook.
- Added `humhub\modules\user\authclient\interfaces\PasswordAuth` — declares the contract for AuthClients that authenticate via the login form (password-based):
  ```php
  interface PasswordAuth {
      public function authenticate(string $username, string $password): bool;
  }
  ```
  `BaseFormClient` and its subclasses (Password, LdapAuth) now implement it. The old stateful pattern — set `$client->login = $loginForm`, then call `$client->auth()` — is replaced by explicit parameter passing. Custom form-auth modules need to rename `auth()` → `authenticate(string, string): bool` and read credentials from the parameters instead of `$this->login->...`. Implementations must still call `setUserAttributes()` on success so the downstream lookup in `AuthClientService::getUser()` works; the `User` lookup itself no longer happens inside `authenticate()`.
- `humhub\modules\user\authclient\AuthAction` now dispatches `CustomAuth` clients before falling through to the OAuth/OpenID families. `AuthController::actions()['external']` uses the HumHub AuthAction class again.
  - The `rememberMe` query-parameter handling (writing to `loginRememberMe` session key) is removed; remember-me for OAuth/SSO clients was never supported anyway
- Removed `humhub\modules\user\jobs\SyncUsers` — was deprecated since 1.16; register a dedicated sync job in your module instead (see `humhub\modules\ldap\jobs\LdapSyncJob` as example)
  - `humhub\modules\user\authclient\interfaces\AutoSyncUsers` is kept for now but no longer called by core — implement a dedicated queue job instead
- Removed from `humhub\modules\user\services\AuthClientService`:
  - `createRegistration()` — use `createUser()` instead
  - `legacySyncAttributes()` — implement `UserSourceInterface::updateUser()` on your UserSource instead
- Removed from `humhub\modules\user\services\AuthClientUserService`:
  - `getPrimaryAuthClient()` — use `UserSourceService::getForUser($user)->getUserSource()` instead
  - `canChangePassword()`, `canChangeEmail()`, `canChangeUsername()`, `canDeleteAccount()`, `getSyncAttributes()` — use `UserSourceService` directly
- Removed from `humhub\modules\user\models\User`:
  - `getUserSourceService()` — use `UserSourceService::getForUser($user)` instead
- Refactored `ldap` module: replaced `laminas/laminas-ldap` with `directorytree/ldaprecord`
  - Removed class `humhub\modules\ldap\components\ZendLdap`
  - Removed from `humhub\modules\ldap\authclient\LdapAuth`:
    - Property `$loginFilter`
    - Methods `getLdap()`, `setLdap()`, `getUserNode()`, `getUserDn()`, `getUserCollection()`, `getAuthClientInstance()`
  - Added `humhub\modules\ldap\services\LdapService` as the new LDAP connection and query layer
  - Added `LdapAuth::getLdapService()` returning `LdapService`
  - `cleanLdapResponse()` moved from `LdapService` to `humhub\modules\ldap\helpers\LdapHelper`
- Refactored `ldap` module to the connection-registry pattern (reference implementation of the UserSource architecture):
  - New `humhub\modules\ldap\connection\LdapConnectionConfig` — plain value object holding hostname, port, baseDn, bindDn, attribute mappings, etc.
  - New `humhub\modules\ldap\connection\LdapConnectionRegistry` — keyed registry of connections; instantiated lazily on `Module::getConnectionRegistry()`. The default `'ldap'` connection is populated from the DB-backed `LdapSettings` UI; additional read-only connections can be added via `protected/config/common.php` → `modules.ldap.connections.<id> = [...]`.
  - `humhub\modules\ldap\authclient\LdapAuth` is now a vanilla `BaseFormClient` that references its connection by ID (`$connectionId`). Connection parameters (hostname/port/baseDn/bindUsername/bindPassword/userFilter/idAttribute/usernameAttribute/emailAttribute/languageAttribute/ignoredDNs/networkTimeout/disableCertificateChecking/autoRefreshUsers/syncUserTableAttributes) are gone — read them from `LdapConnectionConfig` via `LdapAuth::getConfig()` if needed.
  - `LdapAuth` no longer implements `SerializableAuthClient` — it carries no connection state.
  - `LdapUserSource` is registered once per connection. The constructor takes `$connectionId` (not an `LdapAuth` instance). Sync uses the registered AuthClient for attribute normalisation but doesn't own it.
  - `LdapService` constructor now takes `LdapConnectionConfig` (was `LdapAuth`). Obtain instances via `Module::getConnectionRegistry()->getService($connectionId)`. The static `LdapService::create()` factory and `LdapService::getAuthClients()` are removed — use `LdapConnectionRegistry::getService($id)` and `LdapService::getAllUserEntries()` respectively.
  - `LdapSettings::getLdapAuthDefinition()` and `getLdapUserSourceDefinition()` removed — use `LdapSettings::getConnectionConfig()` which returns an `LdapConnectionConfig`.
  - `LdapAuth::$connectionId` and `LdapUserSource::$connectionId` are now required (no default `'ldap'` fallback) — instantiating either class without a connection ID throws `InvalidConfigException`. The bootstrap registers them per connection ID from the registry.
  - The LDAP UserSource is now registered via its own event hook on `UserSourceCollection::EVENT_BEFORE_USER_SOURCES_SET` (`Events::onUserSourceCollectionSet`), separate from the AuthClient registration on `Collection::EVENT_BEFORE_CLIENTS_SET`. The two collections are no longer coupled through a single event handler.
- `MigrateController::$includeModuleMigrations` is now `true` by default
- SiteIcon: Remove support for manually uploaded `@web/uploads/icon/` icons
- New `AssetImage` class
  - `LogoImage`, `SiteIcon`, `LoginBackground`, `MailHeader`, `ProfileImage`, `ProfileBannerImage` are now deprecated or removed.
    - `Space|User::getProfileImage()` => `Space|User::image()`, `Space|User::profileImage` => `Space|User::image`
    - `Space|User::getProfileBannerImage()` => `Space|User::bannerImage()`, `Space|User::profileBannerImage` => `Space|User::bannerImage`
    - `SiteLogo|SiteIcon|LoginBackground|MailHeader::getUrl()` => `Yii::$app->img->logo|icon|loginBackground|mailHeader->getUrl()`
- `AssetManager::forcePublish()` removed
- Removed `@filestore` Alias
- Removed `AssetManager::$preventDefer` option
- New Flysystem Filesystem Wrapper - Migrate all file access for assets and uploads to the Flysystem wrapper (`Yii::$app->fs->getDataMount()` or `Yii::$app->fs->getAssetsMount()`). Read more: https://flysystem.thephpleague.com/docs/usage/filesystem-api/

## Released versions

- [Version 1.18](module-migrate-1.18.md) — captcha framework, Codeception 5, mailer config keys
- [Version 1.17](module-migrate-1.17.md) — Manage-All-Content permission, CSS variables
- [Version 1.16](module-migrate-1.16.md) — search refactor, PHP 8.0 minimum
- [Version 1.15](module-migrate-1.15.md) — JS nonces, type restrictions, GUID validation
- [Legacy versions (1.14 and earlier)](module-migrate-legacy.md)
