# Theme Structure

A theme is a directory under `themes/` (or under a theme module's `themes/<id>/`). Only the files you actually want to override need to be present — everything else comes from the parent theme or core.

## Folder layout

```
themes/mytheme/
├── scss/
│   ├── variables.scss     # $baseTheme + variable overrides
│   ├── build.scss         # entry point, compiled on cache flush
│   ├── _theme.scss        # your own rules
│   ├── _mixins.scss       # optional: SCSS mixin overrides
│   └── _root.scss         # optional: CSS custom-property overrides
├── views/                 # optional: view overrides (see theme-views.md)
├── img/                   # optional: default images, logos
├── js/                    # optional: theme JavaScript
└── font/                  # optional: web fonts
```

Do not edit the bundled `HumHub` or `enterprise` themes directly — updates overwrite them. Create a separate folder and derive from one of them via `$baseTheme`.

## Parent theme

Set the parent at the top of `scss/variables.scss`:

```scss
$baseTheme: "HumHub";       // Community Edition
// $baseTheme: "enterprise"; // Enterprise Edition
```

With a parent set, the theme component loads variables, styles and views from the parent first, then applies your overrides on top. Unmodified view files do not need to exist in your theme — the parent's are used automatically.

Lookup order for any view:

1. Active theme
2. Parent theme (recursive)
3. Core view

## Variables

`variables.scss` carries the Sass variables that drive the colour palette, spacing, typography, etc. Copy individual variables from `humhub/static/scss/variables.scss` into your theme's `variables.scss` and adjust them.

All Sass variables are mirrored to CSS custom properties on `:root`, so prefer the CSS form (`var(--my-variable)`) in your rules — it cooperates with runtime theme switching (e.g. dark mode).

Variables are also accessible in PHP via the theme component, mainly useful in mail templates where CSS variables are not supported:

```php
Yii::$app->view->theme->variable('background-color-page');
```

## Build

SCSS is compiled to CSS on cache flush (*Administration → Settings → Flush Cache*). There is no separate build step for themes.

## Suppressing default component imports

The core `build.scss` imports a number of optional partials (login, modals, …). Each can be suppressed by setting a flag *before* the parent build runs:

```scss
// scss/build.scss
$prev-login: true;   // skip core's login.scss
$prev-modal: true;   // skip core's modal.scss
```

See the [`build.scss` source](https://github.com/humhub/humhub/blob/master/static/scss/build.scss) for the full list of `$prev-*` flags.
