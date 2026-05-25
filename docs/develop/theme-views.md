# View and Mail Overrides

A theme can replace any view file by mirroring its path under the theme's `views/` directory. View overrides should be kept to a minimum — core view files change between releases and every override is a maintenance liability. Prefer SCSS overrides where you can.

## Finding the view to override

Every view file lives in the `views/` folder of the module or package that uses it. Two categories:

- **Controller views** — `<module>/views/<controller>/<action>.php`
- **Widget views** — `<module>/widgets/views/<widget>.php`

When in doubt, grep the codebase for a distinctive piece of markup — e.g. `id="login-form"` lands you in `humhub/modules/user/views/auth/login.php`.

## Override path

Copy the file into `themes/<theme>/views/`, keeping the same path *relative to the module root*. Files outside any module (under `protected/humhub/widgets/`, `protected/humhub/views/`) use `humhub` as their virtual base folder.

### Module views

| Original                                                    | Themed                                                       |
|-------------------------------------------------------------|--------------------------------------------------------------|
| `protected/humhub/modules/admin/views/user/add.php`         | `themes/mytheme/views/admin/views/user/add.php`              |
| `protected/humhub/modules/user/widgets/views/userListBox.php` | `themes/mytheme/views/user/widgets/views/userListBox.php`  |
| `protected/modules/polls/views/poll/show.php`               | `themes/mytheme/views/polls/views/poll/show.php`             |

### Core views

| Original                                                | Themed                                                              |
|---------------------------------------------------------|---------------------------------------------------------------------|
| `protected/humhub/widgets/views/logo.php`               | `themes/mytheme/views/humhub/widgets/views/logo.php`                |
| `protected/humhub/widgets/mails/views/mailHeadline.php` | `themes/mytheme/views/humhub/widgets/mails/views/mailHeadline.php`  |
| `protected/humhub/views/error/index.php`                | `themes/mytheme/views/humhub/error/index.php`                       |

After saving a new override, flush the cache to make the theme component re-scan the file system.

## Overrides via configuration

Since 1.19 view overrides can also be declared directly in `protected/config/common.php`, without creating a theme. This is convenient for small, one-off overrides — the override file can live anywhere on disk.

```php
return [
    'components' => [
        'view' => [
            'theme' => [
                'pathMap' => [
                    // per-file override (key must end in .php)
                    '@humhub/modules/user/views/auth/login.php' => '@config/views/login.php',

                    // directory override (Yii2 default semantics)
                    '@humhub/modules/space/widgets/views' => '@config/views/space-widgets',

                    // multiple fallbacks per source, first existing file wins
                    '@humhub/views/error/index.php' => [
                        '@config/views/error-tenant-a.php',
                        '@config/views/error-default.php',
                    ],
                ],
            ],
        ],
    ],
];
```

Notes:

- Keys ending in `.php` are matched as exact source-file paths. Any other key is treated as a directory prefix and behaves like Yii2's native [pathMap](https://www.yiiframework.com/doc/api/2.0/yii-base-theme#$pathMap-detail).
- Entries from `pathMap` take precedence over the active theme. The active theme is consulted only if no `pathMap` entry resolves to an existing file.
- The mapping survives runtime theme switches — `pathMap` from `common.php` is merged into whichever theme the admin activates in the UI.

## Mail layouts

Mail templates live in `protected/humhub/views/mail/layouts/`. Override them at `themes/mytheme/views/mail/layouts/`.

Inline CSS support in mail clients is limited, so refer to theme variables directly in the markup rather than expecting CSS variables to resolve:

```php
<body style="background-color: <?= Yii::$app->view->theme->variable('background-color-page') ?>;">
    ...
</body>
```

## Default images

To replace HumHub's default placeholder images, drop a same-named file into `themes/<theme>/img/`. The theme component picks it up automatically.

| File                 | Used for                                  |
|----------------------|-------------------------------------------|
| `default_user.jpg`   | User profile image fallback               |
| `default_space.jpg`  | Space image fallback                      |
| `default_banner.jpg` | Banner image fallback (user and space)    |
| `default_module.jpg` | Module image fallback                     |
