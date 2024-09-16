Module Migration Guide to Bootstrap 5
=====================================

## Changes to apply to modules

### Bootstrap widgets

Name spaces starting with `yii\bootstrap` are now `yii\bootstrap5` (a compatibility layer is provided, but will be removed in the future).

But you shouldn't use Bootstrap widgets directly from the external library. Use HumHub ones instead. E.g., use `humhub\widgets\bootstrap\Html` instead of `\yii\bootstrap5\Html`. If a Bootstrap widget is not available, create an issue on https://github.com/humhub/humhub/issues). See the [Code Style wiki page](https://community.humhub.com/s/contribution-core-development/wiki/201/code-style#widgets).

### Removed

- `humhub\widgets\ActiveForm` use `humhub\widgets\bootstrap\ActiveForm` instead

### New

- `humhub\widgets\bootstrap\Badge` (see https://getbootstrap.com/docs/5.3/components/badge/)
- `humhub\widgets\bootstrap\Button::secondary()`
- `humhub\widgets\bootstrap\Button::asBadge()`
- `humhub\widgets\bootstrap\Button::light()`
- `humhub\widgets\bootstrap\Button::dark()`

### Deprecations

- `humhub\modules\ui\form\widgets\ActiveField` use `humhub\widgets\bootstrap\ActiveField` instead
- `humhub\modules\ui\form\widgets\ActiveForm` use `humhub\widgets\bootstrap\ActiveForm` instead
- `humhub\modules\ui\form\widgets\ContentHiddenCheckbox` use `humhub\widgets\bootstrap\ContentHiddenCheckbox` instead
- `humhub\modules\ui\form\widgets\ContentVisibilitySelect` use `humhub\widgets\bootstrap\ContentVisibilitySelect` instead
- `humhub\modules\ui\form\widgets\FormTabs` use `humhub\widgets\bootstrap\FormTabs` instead
- `humhub\modules\ui\form\widgets\SortOrderField` use `humhub\widgets\bootstrap\SortOrderField` instead
- `humhub\libs\Html` use `humhub\widgets\bootstrap\Html` instead
- `humhub\widgets\Tabs` use `humhub\widgets\bootstrap\Tabs` instead
- `humhub\widgets\Button` use `humhub\widgets\bootstrap\Button` instead
- `humhub\widgets\Label` use `humhub\widgets\bootstrap\Badge` instead
- `humhub\widgets\BootstrapComponent`
- `Button::defaultType()` use `Button::secondary()` instead
- `humhub\modules\topic\widgets\TopicLabel` use `humhub\modules\topic\widgets\TopicBadge` instead


## Replacements in HTML attributes

These replacements must also be done in CSS and JS files.

- `pull-left` -> `float-start`
- `pull-right` -> `float-end`
- `text-left` -> `text-start`
- `text-right` -> `text-end`
- `img-responsive` -> `img-fluid` (buttons should be created by the `BaseImage` widget)
- `btn-xs` -> `btn-sm` (buttons should be created by the `Button` widget)
- `btn-group-xs` -> `btn-group-sm`
- `hidden-xs` -> `d-none d-sm-block`
- `img-rounded` -> `rounded`
- `media-object img-rounded` -> `rounded`
- `data-toggle` -> `data-bs-toggle`
- `data-target` -> `data-bs-target`
- `data-dismiss` -> `data-bs-dismiss`
- `dropdown-menu-left` -> `dropdown-menu-start`
- `dropdown-menu-right` -> `dropdown-menu-end`
- `no-space` -> `m-0 p-0`
- `media` (in the HTML class attribute only) -> `d-flex`
- `media-body` -> `flex-grow-1`
- `media-left` -> `flex-shrink-0`
- `media-right` -> `flex-shrink-0 order-last`
- `btn-default` -> `btn-secondary`
- `alert-default` -> `alert-secondary`
- `badge-default` -> `text-bg-secondary`
- `badge-primary` -> `text-bg-primary`
- `badge-danger` -> `text-bg-danger`
- `badge-warning` -> `text-bg-warning`
- `badge-info` -> `text-bg-info`
- `badge-success` -> `text-bg-success`
- `col-xs-` -> `col- `
- Remove `jumbotron` class
- Search for all `label` classes (search for `label label-` and the regex expression `<\w+\s+[^>]*class\s*=\s*["'](?:[^"']*\s)?label(?:\s[^"']*)?["'][^>]*>`) and use the new `humhub\widgets\bootstrap\Badge` widget instead

### Drop down menus

Search for `dropdown-menu` in the code and add `dropdown-item` class to all link items ([see documentation with example](https://getbootstrap.com/docs/5.3/components/dropdowns/#examples)).

## Documentation

- BS3 to BS4: https://getbootstrap.com/docs/4.6/migration/ and https://www.yiiframework.com/wiki/2556/yii2-upgrading-to-bootstrap-4
- BS4 to BS5: https://getbootstrap.com/docs/5.3/migration/ and https://github.com/humhub/yii2-bootstrap5/blob/master/docs/guide/migrating-yii2-bootstrap.md
- BS3 to BS5: https://nodebb.org/blog/nodebb-specific-bootstrap-3-to-5-migration-guide/
