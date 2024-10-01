Module and Theme Migration Guide to Bootstrap 5
=====================================

## Mandatory changes for modules to work with Bootstrap 5

- Classes extending `\humhub\modules\content\widgets\WallCreateContentForm` must replace the required class for the `$form` param: `\humhub\modules\ui\form\widgets\ActiveForm` -> `\humhub\widgets\form\ActiveForm`
- "Dropdown" replacements in HTML attributes (see bellow)

## Removed

- `humhub\widgets\ActiveForm` use `humhub\widgets\bootstrap\ActiveForm` instead

## New

- `humhub\widgets\bootstrap\Badge` (see https://getbootstrap.com/docs/5.3/components/badge/)
- `humhub\widgets\bootstrap\Alert` (see https://getbootstrap.com/docs/5.3/components/alerts/)
- `humhub\widgets\bootstrap\Button::secondary()`
- `humhub\widgets\bootstrap\Button::asBadge()`
- `humhub\widgets\bootstrap\Button::light()`
- `humhub\widgets\bootstrap\Button::dark()`

## Deprecations

- `humhub\modules\ui\form\widgets\ActiveField` use `humhub\widgets\form\ActiveField` instead
- `humhub\modules\ui\form\widgets\ActiveForm` use `humhub\widgets\form\ActiveForm` instead
- `humhub\modules\ui\form\widgets\SortOrderField` use `humhub\widgets\form\SortOrderField` instead
- `humhub\modules\ui\form\widgets\ContentHiddenCheckbox` use `humhub\widgets\form\ContentHiddenCheckbox` instead
- `humhub\modules\ui\form\widgets\ContentVisibilitySelect` use `humhub\widgets\form\ContentVisibilitySelect` instead
- `humhub\modules\ui\form\widgets\FormTabs` use `humhub\widgets\bootstrap\FormTabs` instead
- `humhub\libs\Html` use `humhub\helper\Html` instead
- `humhub\widgets\Tabs` use `humhub\widgets\bootstrap\Tabs` instead
- `humhub\widgets\Button` use `humhub\widgets\bootstrap\Button` instead
- `humhub\widgets\Link` use `humhub\widgets\bootstrap\Link` instead
- `humhub\widgets\Label` use `humhub\widgets\bootstrap\Badge` instead
- `humhub\widgets\bootstrap\Button::xs()` use `humhub\widgets\bootstrap\Button::sm()` instead
- `humhub\widgets\bootstrap\Badge::xs()` use `humhub\widgets\bootstrap\Badge::sm()` instead
- `humhub\widgets\bootstrap\Button::defaultType()` use `humhub\widgets\bootstrap\Button::secondary()` instead
- `humhub\widgets\bootstrap\Badge::defaultType()` use `humhub\widgets\bootstrap\Badge::secondary()` instead
- `humhub\widgets\bootstrap\Button::htmlOptions` use `humhub\widgets\bootstrap\Button::options` instead
- `humhub\widgets\bootstrap\Badge::htmlOptions` use `humhub\widgets\bootstrap\Badge::options` instead
- `humhub\widgets\BootstrapComponent`
- `Button::defaultType()` use `Button::secondary()` instead
- `humhub\modules\topic\widgets\TopicLabel` use `humhub\modules\topic\widgets\TopicBadge` instead
- `humhub\widgets\Modal` use `humhub\widgets\modal\JsModal` instead
- `humhub\widgets\ModalDialog` use `humhub\widgets\modal\Modal` instead, which is different, as it's for the full Modal box, not just the dialog part of it
- `humhub\widgets\ModalDialog::begin()` use `humhub\widgets\modal\Modal::beginDialog()` instead
- `humhub\widgets\ModalDialog::end()` use `humhub\widgets\modal\Modal::endDialog()` instead
- `humhub\widgets\modal\JsModal::header` & `humhub\widgets\modal\Modal::header`: use `title` instead
- `humhub\widgets\modal\JsModal::animation` & `humhub\widgets\modal\Modal::animation` (all modal boxes are opened with the fade animation)
- `humhub\widgets\modal\JsModal::centerText` & `humhub\widgets\modal\Modal::centerText`
- `humhub\widgets\modal\JsModal::size` & `humhub\widgets\modal\Modal::size` values: use `Modal::SIZE_DEFAULT`, `Modal::SIZE_SMALL`, `Modal::SIZE_LARGE`, `Modal::SIZE_EXTRA_LARGE` instead of (`normal`, `extra-small`, `small`, `medium`, and `large`)
- `humhub\widgets\ModalButton` use `humhub\widgets\modal\ModalButton` instead
- `humhub\widgets\ModalClose` use `humhub\widgets\modal\ModalClose` instead
- `humhub\widgets\GlobalModal` use `humhub\widgets\modal\GlobalModal` instead
- `humhub\widgets\GlobalConfirmModal` use `humhub\widgets\modal\GlobalConfirmModal` instead


## Bootstrap widgets

Name spaces starting with `yii\bootstrap` are now `yii\bootstrap5` (a compatibility layer is provided, but will be removed in the future).

But you shouldn't use Bootstrap widgets directly from the external library. Use HumHub ones instead. E.g., use `humhub\widgets\bootstrap\Html` instead of `\yii\bootstrap5\Html`. If a Bootstrap widget is not available, create an issue on https://github.com/humhub/humhub/issues). See the [Code Style wiki page](https://community.humhub.com/s/contribution-core-development/wiki/201/code-style#widgets).


## Replacements in HTML attributes

These replacements must be done in PHP, SCSS (formerly LESS) and JS files.

### General replacements

- `img-responsive` -> `img-fluid` (use the `humhub\modules\ui\widgets\BaseImage` widget when possible)
- `alert-default` -> `alert-secondary` (use the `humhub\widgets\bootstrap\Alert` widget when possible)
- `btn-xs` -> `btn-sm` (use the `humhub\widgets\bootstrap\Button` widget when possible)
- `btn-default` -> `btn-secondary`
- `pull-left` -> `float-start`
- `pull-right` -> `float-end`
- `text-left` -> `text-start`
- `text-right` -> `text-end`
- `btn-group-xs` -> `btn-group-sm`
- `hidden-xs` -> `d-none d-sm-block` or `d-none d-sm-inline` or `d-none d-sm-flex` (depending on the desired display mode)
- `img-rounded` -> `rounded`
- `media-object img-rounded` -> `rounded`
- `data-toggle` -> `data-bs-toggle`
- `data-target` -> `data-bs-target`
- `data-dismiss` -> `data-bs-dismiss`
- `no-space` -> `m-0 p-0`
- `align-center` -> `text-center` or `d-flex justify-content-center`
- `col-xs-` -> `col- `
- `input-group-addon` -> `input-group-text` (or `input-group-prepend` or `input-group-append`)
- Remove `jumbotron` class

### Input groups

Remove `<span class="input-group-btn">` button wrapper inside `<div class="input-group">`.

Example:
```html
<div class="input-group">
  <span class="input-group-btn">
    <button class="btn btn-default">My action</button>
  </span>
</div>
```

Should be replaced with:
```html
<div class="input-group">
  <button class="btn btn-secondary">My action</button>
</div>
```

### Hidden classes

In the following replacements, you replace `block` with `inline` or `flex` (depending on the desired display mode).
E.g., you can use `d-sm-inline` or `d-sm-flex` instead of `d-sm-block`.

- `hidden-xs` -> `d-none d-sm-block` or `d-none d-sm-inline` or `d-none d-sm-flex` (depending on the desired display mode)
- `hidden-sm` → `d-sm-none d-md-block` (idem, replace `block` with `inline` or `flex`)
- `hidden-md` → `d-md-none d-lg-block`
- `hidden-lg` → `d-lg-none d-xl-block`
- `hidden` (search regex expression for HTML tags: `<\w+\s+[^>]*class\s*=\s*["'](?:[^"']*\s)?hidden(?:\s[^"']*)?["'][^>]*>` ; search also in JS for strings such as  `Class('hidden')`, `Class("hidden")` and `Class' => 'hidden'`) -> `d-none`
- `visible-xs` → `d-block d-sm-none`
- `visible-sm` → `d-none d-sm-block d-md-none`
- `visible-md` → `d-none d-md-block d-lg-none`
- `visible-lg` → `d-none d-lg-block d-xl-none`
- `visible` (search regex expression for HTML tags: `<\w+\s+[^>]*class\s*=\s*["'](?:[^"']*\s)?visible(?:\s[^"']*)?["'][^>]*>` ; search also in JS for strings such as  `Class('visible')`, `Class("visible")` and `Class' => 'visible'`) → `d-block`

### Dropdown

- Search for `dropdown-menu` in the code and add `dropdown-item` class to all link items ([see documentation with example](https://getbootstrap.com/docs/5.3/components/dropdowns/#examples)).
- Search for `divider` classes (search regex expression for HTML tags: `<\w+\s+[^>]*class\s*=\s*["'](?:[^"']*\s)?divider(?:\s[^"']*)?["'][^>]*>`) and replace with `dropdown-divider`
- `dropdown-menu-left` -> `dropdown-menu-start`
- `dropdown-menu-right` -> `dropdown-menu-end`
- Remove `<span class="caret"></span>` and `<b class="caret"></b>` in dropdown buttons (as there are already added by Bootstrap 5 via the `:after` pseudo-element)

## Deprecated Bootstrap 3 components

They have been removed from Bootstrap 5, but will still be supported in HumHub for a while.

See `static/scss/_bootstrap3.scss` for the full list of deprecations.

### Panel

Should be replaced with cards.

TODO in core and to document here

### Well

Should be replaced with cards.

TODO in core and to document here

### Label & Badge

Search for all `label` classes (search for `label label-` and the regex expression `<\w+\s+[^>]*class\s*=\s*["'](?:[^"']*\s)?label(?:\s[^"']*)?["'][^>]*>`) and use the new `humhub\widgets\bootstrap\Badge` widget instead

Doc: https://getbootstrap.com/docs/5.3/components/badge/

For existing badges, also use the `humhub\widgets\bootstrap\Badge` widget when possible.

Replacements:
- `badge-default` -> `text-bg-secondary`
- `badge-primary` -> `text-bg-primary`
- `badge-danger` -> `text-bg-danger`
- `badge-warning` -> `text-bg-warning`
- `badge-info` -> `text-bg-info`
- `badge-success` -> `text-bg-success`

### Media

- Search for `media-list` and replace `ul` tag with `div`. E.g. `<ul class="media-list">` -> `<div class="media-list">`
- Inside, replace `li` tags with `div` tags. E.g. `<li class="media">` -> `<div class="d-flex">`
- Search for `media` classes (search regex expression for HTML tags: `<\w+\s+[^>]*class\s*=\s*["'](?:[^"']*\s)?media(?:\s[^"']*)?["'][^>]*>`) and replace with `d-flex`
- `media-heading` -> `mt-0` (removes the top margin, keeping it close to the top of the content area) ; the related HTML tag can be replaced with `h5` or `h4`
- `media-body` -> `flex-grow-1`
- `media-left` -> `flex-shrink-0`
- `media-right` -> `flex-shrink-0 order-last`
- `media-object` -> `flex-shrink-0` (if on an image, encapsulate the image in a `div` tag with `flex-shrink-0` class)
- Remove `float-start` (or `pull-left`) class for images inside a `<div class="flex-shrink-0">`

Doc: https://getbootstrap.com/docs/5.3/utilities/flex/#media-object


## Themes and Modules: LESS is replaced with SCSS

LESS format is not supported anymore.
Use SCSS instead. See https://getbootstrap.com/docs/5.3/customize/sass/

### Convert LESS to SCSS

Rename `less` folder to `scss` and rename all `.less` files to `.scss`.
Prefix all SCSS files with `_` except the `build.scss` file.
E.g.: `less/variables.less` -> `scss/_variables.scss`

Un can use the following tool to convert LESS to SCSS: https://less2scss.awk5.com/
However, you need to check the output manually.

### Variables

- `$default` is deprecated. Use `$light` or `$secondary` instead.
- New variables: `$secondary`, `$light` and `$dark`

In all SCSS files except `_variables.scss`, replace all SCSS variables with CSS variables.
E.g.: `color: $primary` -> `color: var(--primary)`

### Select2 stylesheet

`static/css/select2Theme` folder has been removed, and the SCSS file moved and renamed to `static/scss/_select2.scss`


## Themes

### Build file

The `build.scss` file mustn't import parent theme files anymore, as it is automatically done by the new compiler.

Take example with the `HumHub` community theme.

### Compiler

Grunt compiler has been removed.

Instead, compile your theme online, using the new "(Re)build Theme CSS" button in Administration -> Settings -> Appearance.

If you use the "Updater", you don't need anymore to recompile your theme CSS after updating HumHub core, as it will be done automatically.

But if you upgrade HumHub without this module, you will have to click on the "(Re)build Theme CSS" button after each HumHub core upgrade.

### Overwritten view files

Most of the views have been refactored to use the new Bootstrap 5 HTML tags and classes.

Please review all overwritten view files. See https://community.humhub.com/s/theming-appearance/wiki/134/Migration%3A+Identify+Template+Changes+ for more information.

The most important change concerns the `protected/humhub/views/layouts/main.php` file, which has been refactored with bs5 flex logic (instead of floating right elements).


## Documentation

- BS3 to BS4: https://getbootstrap.com/docs/4.6/migration/ and https://www.yiiframework.com/wiki/2556/yii2-upgrading-to-bootstrap-4
- BS4 to BS5: https://getbootstrap.com/docs/5.3/migration/ and https://github.com/humhub/yii2-bootstrap5/blob/master/docs/guide/migrating-yii2-bootstrap.md
- BS3 to BS5: https://nodebb.org/blog/nodebb-specific-bootstrap-3-to-5-migration-guide/
