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

`variables.scss` carries the Sass variables that drive the colour palette, spacing, typography, etc. Copy individual variables from `protected/humhub/resources/scss/variables.scss` into your theme's `variables.scss` and adjust them. Core variables carry the `!default` flag, and the theme's `variables.scss` is imported before the core one, so the theme's value wins.

The colour variables are mirrored to CSS custom properties on `:root` (`--hh-*`, see `_root.scss`, and Bootstrap's `--bs-*`), so prefer the CSS form (`var(--hh-text-color-main)`) in your rules — it cooperates with runtime theme switching (e.g. dark mode).

Variables are also accessible in PHP via the theme component, mainly useful in mail templates where CSS variables are not supported:

```php
Yii::$app->view->theme->variable('background-color-page');
```

## Build

SCSS is compiled to CSS on cache flush (*Administration → Settings → Flush Cache*). There is no separate build step for themes.

## Cascade layers

The compiled `theme.css` puts its rules into [cascade layers](https://developer.mozilla.org/en-US/docs/Web/CSS/@layer) (`humhub\helpers\ThemeHelper::CSS_LAYERS`):

| Order | Content |
|---|---|
| `@layer core` | Bootstrap's components and helpers, and the core's `build.scss` |
| `@layer theme` | The `build.scss` of the theme (parent theme first) |
| not in a layer | Bootstrap's utilities, the core's `_utilities.scss` and `_vendor.scss`, the custom SCSS of *Administration → Settings → Appearance* |

A later layer wins against an earlier one whatever the specificity of the selectors, and CSS outside of any layer wins against all layers. So a rule of the theme overrides the core without copying the core's selector or adding `!important`:

```scss
// scss/build.scss of the theme: wins against `#topbar-first .nav > li > a` of the core
.topbar a {
    color: var(--hh-text-color-main);
}
```

- Bootstrap and the core share the `core` layer, so the specificity decides between them as before.
- The CSS of modules and of third-party libraries (jQuery UI, blueimp Gallery, …) is not in a layer, it wins against the core and the theme. The core's rules for these libraries are in `_vendor.scss`, outside of the layers too.
- The utilities (`.d-none`, `.mt-2`, …) stay outside of the layers and keep winning (`!important`).
- For `!important` the order of the layers is reversed: an `!important` of the core wins against an `!important` of the theme or of a module. Override such a declaration in the custom SCSS of the Appearance settings, which is not in a layer.
- The variables a core partial declares (e.g. `$data-table-gap` in `_data-table.scss`) can still be set in the theme's `variables.scss`, but a theme's `build.scss` can't read them: they are only defined within the `core` layer. The variables of the core's `variables.scss` and of Bootstrap, and the mixins of `_mixins.scss`, are available everywhere.

## Image sizes

The profile images and space images of the core (`user\widgets\Image`, `space\widgets\Image`, their Vue counterparts `UserImage`/`SpaceImage`, the profile header) set their size inline as `width: var(--hh-image-width, 32px); height: var(--hh-image-height, 32px)`. A theme changes the size by these variables, without `!important`; like every CSS variable they are inherited by the images inside:

```scss
// scss/build.scss of the theme: all profile and space images within a box of the theme
.my-theme-box {
    --hh-image-width: 48px;
    --hh-image-height: 48px;
}
```

The height limit of a richtext editor (`pluginOptions.maxHeight`) is the variable `--hh-richtext-max-height` in the same way.

## Icons

Icons are [Tabler Icons](https://tabler.io/icons), rendered by `humhub\widgets\Icon` as `<i class="ti ti-<name>">`; a filled variant is `ti-<name>-filled`. Style them through `.ti` and `.ti-<name>`, and use a Tabler codepoint with `font-family: tabler-icons` (or `tabler-icons-filled`) for glyphs placed via `content`. The library-neutral size and utility classes `icon-sm`, `icon-lg`, `icon-2x` … `icon-10x`, `icon-fw`, `icon-pull-left`, `icon-pull-right` and `icon-spin` live in the core's `_icon.scss`.

## Suppressing default component imports

The core `build.scss` imports a number of optional partials (login, modals, …). Each can be suppressed by a flag in the theme's `variables.scss` — the theme's `build.scss` is imported after the core one, so a flag set there comes too late:

```scss
// scss/variables.scss
$prev-login: true;   // skip core's _login.scss
$prev-modal: true;   // skip core's _modal.scss
```

See the [`build.scss` source](https://github.com/humhub/humhub/blob/develop/protected/humhub/resources/scss/build.scss) for the full list of `$prev-*` flags.
