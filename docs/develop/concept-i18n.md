# Internationalization

HumHub uses Yii's [i18n system](https://www.yiiframework.com/doc/guide/2.0/en/tutorial-i18n) with a HumHub-specific message-category convention that auto-resolves to your module's `messages/` folder. You don't need to wire `i18n` translations into `config.php` — the resolution is automatic.

Make sure your PHP installation has the `intl` extension enabled — see [Setting Up PHP Environment](https://www.yiiframework.com/doc/guide/2.0/en/tutorial-i18n#setup-environment) for details.

## Message category convention

```php
Yii::t('ExampleModule.some_own_category', 'Translate me');
```

The category before the dot maps to your module's directory in `messages/`. Naming convention:

| Module ID       | Message category     |
|-----------------|----------------------|
| `polls`         | `PollsModule`        |
| `custom_pages`  | `CustomPagesModule`  |

The convention is `PascalCase` of the module ID with `Module` appended.

## Extracting messages

Generate or refresh translation files for a module:

```sh
php yii message/extract-module example
```

The command scans every `Yii::t()` call inside the module and updates the per-language `.php` files under `messages/`.

**Translated modules:** if your module's translations live on [translate.humhub.org](https://translate.humhub.org), do **not** run `extract-module` — the translation platform owns those files. See [module-git → translations](module-git.md#translations).
