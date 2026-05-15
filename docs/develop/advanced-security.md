# Security

Module-side security checklist. The platform-side / operator-side checklist lives at [admin → security](https://docs.humhub.org/docs/admin/security).

## Production mode

A development install runs with `DEBUG=true`. **Never** ship that to production — the debug toolbar exposes the application state, including queries and stack traces. Production mode enables compressed assets, hides debug output and turns on caching.

See [environment → production mode](intro-environment.md#production-mode) for the switch and [build → production assets](intro-build.md#build-production-assets) for the asset build.

### Enable production mode

Set in `.env`:

```env
HUMHUB_CONFIG__PARAMS__INSTALLER__STATE=finished
HUMHUB_CONFIG__COMPONENTS__ASSETMANAGER__BUNDLES_PROD=true
```

Or via `protected/config/common.php` — see [admin → advanced configuration](https://docs.humhub.org/docs/admin/advanced-configuration).

## Module security

Follow the [Yii security best practices](https://www.yiiframework.com/doc/guide/2.0/en/security-best-practices). HumHub-specific things to check:

### Authorisation

- Use [permissions](concept-permissions.md) for every action that mutates state. Don't gate access via UI alone — controllers run independently of the views that link to them.
- For container-bound actions, use [`ContentContainerController`](module-content.md#contentcontainercontroller) — it integrates with the access rules and inserts the container check before your action runs.
- Restrict guest access deliberately. `Yii::$app->user->isGuest` may be `true` even when reaching your controller; either block at the access-rule level (`['login']`) or null-check before dereferencing `Yii::$app->user->identity`.

### Input

- [Validate every form / request input](https://www.yiiframework.com/doc/guide/2.0/en/input-validation) via model `rules()`.
- Encode output destined for HTML with `humhub\libs\Html::encode()`. The Yii equivalent works too; HumHub's adds RichText handling.
- Use parameterised queries / ActiveRecord finders. The [SQL injection section](https://www.yiiframework.com/doc/guide/2.0/en/security-best-practices#avoiding-sql-injections) of the Yii guide covers what *not* to do.

### CSRF

POST / PUT / DELETE requests require a CSRF token. Yii handles this automatically for forms rendered via `ActiveForm`, AJAX submitted via the `humhub.client` JS module, and any request triggered via a `data-action-*` attribute. If you bypass these — hand-rolled fetch, third-party JS — include the CSRF token explicitly:

```js
var csrf = humhub.modules.client.csrf;
fetch(url, { headers: { 'X-CSRF-Token': csrf } });
```

### JavaScript nonces

Since HumHub 1.15 the core enables JavaScript nonces by default (CSP-style). All inline `<script>` tags need the current request's nonce. The `Html` helper applies it automatically — prefer it over manually emitting `<script>` blocks.

### File uploads

The [file module](concept-files.md) already enforces access control via the parent record. Don't add file-serving endpoints that bypass that path.
