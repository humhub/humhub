# Sudo mode

**Since 1.20.** Sensitive actions ask the user to confirm their identity first: changing the
e-mail address, the username or the password, deleting the account, creating a personal access
token, and (when switched on) the whole administration. The confirmation is remembered per
session for a sliding window, so a run of work in the administration asks once, not on every
page.

Before, each of these forms carried its own "current password" field and validated it with
`CheckPasswordValidator`; the personal access token form had its own re-authentication rule
(`oauth\components\RecentAuthentication`). Both are replaced by one component.

## State

Sudo mode is a **session state**: the time of the last confirmation, `lastSudoAt`, bound to the
user id of the session. It is **active** while

```
now - lastSudoAt <= idle window        (default 15 minutes, see Settings)
```

Every protected request that passes **refreshes** `lastSudoAt`. The window slides with the
user's activity in protected areas and only runs out after a pause.

**What starts it:**

- An **interactive login** through the login controller (`AuthController::EVENT_AFTER_LOGIN`):
  password, LDAP, SSO. Right after signing in nothing asks again.
- A **confirmation** in the dialog or on the confirmation page.

**What does not count:**

- A login by remember-me cookie (`User::EVENT_AFTER_LOGIN` with `cookieBased`) drops any
  recorded state; only the login controller records one.
- **Impersonation.** While an admin impersonates a user, sudo mode is never active, whatever
  the session recorded before (fail closed, as `RecentAuthentication` did).
- Logout and session end.

## Confirming

How a user confirms depends on what they can confirm with:

| User | Factor | In the dialog |
|---|---|---|
| Has a password client: local password, LDAP | The password, checked by the same `PasswordAuth` clients as the login form (`BaseFormClient::authenticate()`); the client has to resolve to the current user | Password field |
| No password client (SSO only, account without a password) | A **fresh login** through the user's current auth client, with the requested page as return URL: the session is ended locally (not through the logout action, so no Single Logout reaches the IdP) and the client's login starts. The completed login starts sudo mode and the user comes back. An IdP with a live session returns at once; one without asks for credentials, which is the re-authentication wanted. | Button "Sign in again" |

**Throttling:** 5 failed confirmations per user in 15 minutes, counted in the cache. While
throttled, confirmations are refused with "Too many attempts, try again later." (the rule of
`RecentAuthentication`).

Other factors, e.g. a 2FA code, are not part of this concept.

## Enforcement

`humhub\modules\user\components\SudoFilter`, a Yii `ActionFilter`, protects actions. When sudo
mode is active, the request passes and the window is refreshed. When it is not, the filter
answers according to the request class of the [gates](user-gates.md) (`RequestClass`):

| Request | Answer |
|---|---|
| Full page (browser navigation) | `302` to `user/sudo/confirm` with the requested URL as return URL; a form posted without JavaScript returns to its page and has to be sent again |
| AJAX, pjax, a JSON endpoint (the API with the browser session) | `401`, JSON `{"sudo": true, "url": "<confirm url>"}`, header `X-Sudo-Required: <confirm url>` |
| API with a token | Never intercepted: API access is governed by scopes, not by sudo mode |
| Any, while impersonating | `403`: nobody can confirm for the impersonated user |

The filter is attached to:

| Where | Actions |
|---|---|
| `user/account` | `change-username`, `change-email`, `change-password` |
| `user/security` | `delete` |
| `oauth/account` | `create-token` |
| `oauth/authorize` | `index`: a grant is as strong as a personal access token |
| `admin\components\Controller` (and every module controller extending it) | all, only while the setting "Require password confirmation for the administration" is on |
| API controllers with `BaseController::$administration` (the administration's tables, OAuth clients and grants, the marketplace) | all, for the browser session, only while the same setting is on |

Not protected: `user/account/change-email-validate` (the link from the confirmation e-mail
carries its own token), the must-change-password flow (a gate with its own password check),
and anything a module does not protect itself (see Extending).

The forms lose their `currentPassword` attribute and field: `AccountChangeEmail`,
`AccountChangeUsername`, `Password` (scenario `changePassword`), `AccountDelete`,
`PersonalAccessTokenForm`. `CheckPasswordValidator` stays for the remaining users.

## The dialog and the page

`user/sudo/confirm` renders one form in two frames:

- **Dialog** (the regular [Dialog](../ui/dialog.md), 400px): title "Confirm your password",
  one line "To continue, confirm it's you.", the password field (or the "Sign in again"
  button), footer Cancel / Confirm. Wrong password and throttling show inline at the field.
- **Page**: the same card centred on the page, for full-page requests. Confirming redirects to
  the return URL.

**Client:** `humhub.client` recognises the `401` with `X-Sudo-Required` centrally and hands it
to `humhub.ui.sudo`, which opens the dialog from the URL and, after a successful confirmation,
**repeats the original request**: the same AJAX call for actions and form submits, the pjax
navigation for a page. Cancel closes the dialog and leaves the page as it was. Clicking
"Administration" in the account menu therefore opens the dialog over the current page and then
continues into the administration.

**Accessibility:** the dialog follows the dialog rules (focus on the field, Esc cancels, the
error is linked to the field and announced).

## Settings

Administration › Users › Settings (`AuthenticationSettingsForm`), next to the idle timeout:

| Setting | Key | Default |
|---|---|---|
| Require password confirmation for the administration | `auth.sudoAdmin` | off |
| Confirmation stays valid for (minutes) | `auth.sudoIdleMinutes` | 15 (minimum 1) |

The account actions and personal access tokens are always protected, as they asked for the
password before.

## Extending

A module protects its own sensitive actions by attaching the filter:

```php
public function behaviors()
{
    return array_merge(parent::behaviors(), [
        'sudo' => ['class' => SudoFilter::class, 'only' => ['delete', 'rotate-secret']],
    ]);
}
```

An API controller serving the module's administration sets `protected bool $administration = true;`,
so the browser session reaches it only in sudo mode while the administration asks for it.

`SudoMode::isActive()` and `SudoMode::confirm()` are public for other uses (a module that
wants to hide a button until confirmed, a module with a flow of its own).

## Migration

- `humhub\modules\oauth\components\RecentAuthentication` is removed; `SudoMode` replaces it.
- The account forms and `PersonalAccessTokenForm` have no `currentPassword` attribute any more;
  views or tests filling that field need updating.
- Modules with their own "current password" fields can drop them and attach `SudoFilter`.

## Tests

- **Unit** (`SudoMode`): window and refresh, start by login, cookie login drops the state,
  impersonation never active, throttle, factor selection (password client / none).
- **Functional**: filter answers per request class; the account actions and the token form
  are refused without sudo mode and pass with it; the administration only with the setting on;
  the confirm action with right, wrong and throttled passwords; the OAuth authorization (also
  with *skip consent*) only in sudo mode.
- **API** (`AdminSudoApiCest`): with the setting on, the administration's endpoints answer the
  browser session with `401` until sudo mode is active; a token passes.
- **Acceptance**: the dialog on the account page and the repeated request after confirming.
