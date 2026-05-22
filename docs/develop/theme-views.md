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
