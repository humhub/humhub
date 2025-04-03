Module and Theme Migration Guide to Bootstrap 5
=====================================

## Mandatory changes for modules to work with Bootstrap 5

- Classes extending `\humhub\modules\content\widgets\WallCreateContentForm`: replace `renderActiveForm(\humhub\modules\ui\form\widgets\ActiveForm $form)` with `renderActiveForm(\humhub\widgets\form\ActiveForm $form)`
- "Dropdown" replacements in HTML attributes (see bellow)


## Removed

- `humhub\widgets\ActiveForm` use `humhub\widgets\form\ActiveForm` instead
- `humhub\modules\content\widgets\WallEntryLabels` use `humhub\modules\content\widgets\WallEntryBadges` instead
- `humhub\modules\ui\view\bootstrap\ThemeLoader` use `humhub\components\bootstrap\ThemeLoader` instead
- `humhub\modules\ui\view\components\ThemeVariables` use `humhub\components\ThemeVariables` instead
- `humhub\modules\ui\view\components\ThemeViews` use `humhub\components\ThemeViews` instead
- `humhub\modules\ui\view\components\ViewMeta` use `humhub\components\ViewMeta` instead
- `js/humhub/legacy/jquery.loader.js`
- CSS for User & Space picker


## New

Widgets in these new folders:
- `humhub\widgets\bootstrap`
- `humhub\widgets\form`
- `humhub\widgets\modal`

And especially:
- `humhub\widgets\bootstrap\Badge` (see https://getbootstrap.com/docs/5.3/components/badge/)
- `humhub\widgets\bootstrap\Alert` (see https://getbootstrap.com/docs/5.3/components/alerts/)
- `humhub\widgets\bootstrap\Button::asBadge()`
- `humhub\widgets\bootstrap\Button::secondary()`
- `humhub\widgets\bootstrap\Button::light()`
- `humhub\widgets\bootstrap\Button::dark()`

Colors: `secondary`, `light` and `dark` are the new Bootstrap colors (`default` is deprecated).


## Deprecations

[Bootstrap 3 components](https://getbootstrap.com/docs/3.3/components/) are removed from Bootstrap 5, but those used in HumHub are still supported for a while via the `static/scss/_bootstrap3.scss` compatibility stylesheet

Widgets & helpers:
- `humhub\widgets\BootstrapComponent`
- `humhub\widgets\Tabs` use `humhub\widgets\bootstrap\Tabs` instead
- `humhub\widgets\Button` use `humhub\widgets\bootstrap\Button` instead
- `humhub\widgets\Link` use `humhub\widgets\bootstrap\Link` instead
- `humhub\widgets\Label` use `humhub\widgets\bootstrap\Badge` instead (watch out for class name changes!)
- `humhub\modules\topic\widgets\TopicLabel` use `humhub\modules\topic\widgets\TopicBadge` instead (watch out for class name changes!)
- `humhub\libs\Html` use `humhub\helpers\Html` instead
- `humhub\modules\ui\view\components\View` use `humhub\components\View` instead
- `humhub\modules\ui\view\helpers\ThemeHelper` use `protected/humhub/helpers/ThemeHelper.php` instead

Widget methods & properties:
- `humhub\widgets\bootstrap\Button::xs()` use `humhub\widgets\bootstrap\Button::sm()` instead
- `humhub\widgets\bootstrap\Button::defaultType()` use `humhub\widgets\bootstrap\Button::light()` or `Button::secondary()` instead
- `humhub\widgets\bootstrap\Badge::xs()` use `humhub\widgets\bootstrap\Badge::sm()` instead
- `humhub\widgets\bootstrap\Badge::defaultType()` use `humhub\widgets\bootstrap\Badge::light()` or `Badge::secondary()` instead
- `humhub\widgets\bootstrap\Button::htmlOptions` use `humhub\widgets\bootstrap\Button::options` instead
- `humhub\widgets\bootstrap\Badge::htmlOptions` use `humhub\widgets\bootstrap\Badge::options` instead

Name spaces starting with `yii\bootstrap`: use `yii\bootstrap5` instead (but see "HumHub widgets" bellow)

Forms and Modal Dialog: see bellow.

SCSS and CSS variables: see bellow.


## HumHub widgets

If available, use HumHub widgets instead of the native library widgets.
This will make it easier to migrate to new versions of the external libraries ([see Code Style wiki page](https://community.humhub.com/s/contribution-core-development/wiki/201/code-style#widgets)).

E.g. `\yii\bootstrap5\Html`, use `humhub\widgets\bootstrap\Html` instead.

If a Bootstrap widget is not available, create an issue on https://github.com/humhub/humhub/issues).


## Modal Dialog

### New

- `humhub\widgets\modal\ModalButton::outline()` ([doc](https://getbootstrap.com/docs/5.3/components/buttons/#outline-buttons))

### Deprecations

- `humhub\widgets\Modal` use `humhub\widgets\modal\JsModal` instead
- `humhub\widgets\ModalDialog` use `humhub\widgets\modal\Modal` instead, which is different, as it's for the full Modal box, not just the dialog part of it
- `humhub\widgets\ModalButton` use `humhub\widgets\modal\ModalButton` instead
- `humhub\widgets\modal\ModalButton::submitModal($url, $label)` use `humhub\widgets\modal\ModalButton::save($label, $url)` (watch out the parameter order change!) or `humhub\widgets\modal\ModalButton::primary($label)->submit($url)` instead
- `humhub\widgets\ModalClose` use `humhub\widgets\modal\ModalClose` instead
- `humhub\widgets\GlobalModal` use `humhub\widgets\modal\GlobalModal` instead
- `humhub\widgets\GlobalConfirmModal` use `humhub\widgets\modal\GlobalConfirmModal` instead
- `humhub\widgets\ModalDialog::begin()` use `humhub\widgets\modal\Modal::beginDialog()` instead (see changes in the "Modal Dialog" chapter bellow)
- `humhub\widgets\ModalDialog::end()` use `humhub\widgets\modal\Modal::endDialog()` instead
- `humhub\widgets\modal\Modal::header` & `humhub\widgets\modal\JsModal::header`: use `title` instead
- `humhub\widgets\modal\Modal::animation` & `humhub\widgets\modal\JsModal::animation` (all modal boxes are opened with the fade animation)
- `humhub\widgets\modal\Modal::centerText` & `humhub\widgets\modal\JsModal::centerText`
- `humhub\widgets\modal\Modal::showClose`: use `closeButton` instead (but works differently, see `yii\bootstrap5\Modal::closeButton` doc)
- `humhub\widgets\modal\JsModal::size` & `humhub\widgets\modal\Modal::size` values: use `Modal::SIZE_DEFAULT`, `Modal::SIZE_SMALL`, `Modal::SIZE_LARGE`, `Modal::SIZE_EXTRA_LARGE` instead of (`normal`, `extra-small`, `small`, `medium`, and `large`)

### Replacements in HTML attributes

- `data-backdrop` => `data-bs-backdrop`
- `data-keyboard` => `data-bs-keyboard`

### Usage

`Modal::beginDialog()` (formerly `ModalDialog::begin()`) now includes `<div class="modal-body">` and the footer must be defined as a parameter, similar to the `header` which has been renamed to `title`.

Before:

```php
<?php ModalDialog::begin([
	'header' => Yii::t('ModuleIdModule.base', 'Title'),
]) ?>
	<div class="modal-body">
		Content
	</div>
	<div class="modal-footer">
		<?= ModalButton::cancel(Yii::t('base', 'Close')) ?>
	</div>
<?php ModalDialog::end()?>
```

Now:

```php
<?php Modal::beginDialog([
	'title' => Yii::t('ModuleIdModule.base', 'Title'),
    'footer' => ModalButton::cancel(Yii::t('base', 'Close')),
]) ?>
	Content
<?php Modal::endDialog() ?>
```

If the footer contains a "Submit" button, the modal dialog must be included in the form by using the `Modal::beginFormDialog()` and `Modal::endFormDialog()` methods:

```php
<?php $form = Modal::beginFormDialog([
    'title' => Yii::t('ModuleIdModule.base', 'Title'),
    'footer' => ModalButton::cancel() . ' ' . ModalButton::save(),
    'form' => [], //  configuration for the form (optional)
]) ?>
    Content and the form inputs for $form
<?php Modal::endFormDialog()?>
```

Search also for `<div class="modal-dialog` in the code to find modal dialogs not using the `Modal::beginDialog()` method.

### Sizes

Modals have three other optional sizes, see https://getbootstrap.com/docs/5.3/components/modal/#optional-sizes

Use `'size' => Modal::SIZE_LARGE,` in the `Modal::beginFormDialog()` param array to change the size (or `SIZE_EXTRA_LARGE`, but `SIZE_SMALL` is not recommended).

### Footer

Affirmative buttons (i.e., CTA buttons that perform an action) should always be placed on the right side, whereas dismissive buttons should be placed on the left.

E.g., "Close" should be on the left, and "Save" should be on the right.

Affirmative Buttons (Action-Taking):

- Submit - Used for submitting forms.
- Save - Confirms and saves edits.
- Send Invite - Sends an invitation.
- Confirm - Approves and proceeds with an action.
- Publish - Makes content "live".

Dismissive Buttons (Non-Action, Exit, or Revert):

- Cancel - Dismisses an action without saving.
- Close - Closes a modal or window.
- Back - Returns to the previous step.
- Discard - Exits without saving edits.
- Exit - Leaves a process or page.

When it is not possible to place the buttons in the modal footer, use the `modal-body-footer` class:

```php
<?php $form = Modal::beginDialog() ?>
    Content
    <div class="modal-body-footer">
        <?= ModalButton::cancel(Yii::t('base', 'Close')) ?>
        <?= ModalButton::save() ?>
    </div>
<?php Modal::endDialog()?>
```

## Forms

### Widgets deprecations

- `yii\widgets\ActiveForm`, `yii\bootstrap\ActiveForm`, `yii\bootstrap5\ActiveForm`, `kartik\widgets\ActiveForm`, `kartik\form\ActiveForm`, `humhub\modules\ui\form\widgets\ActiveForm`: use `humhub\widgets\form\ActiveForm` instead
- `yii\widgets\ActiveField`, `yii\bootstrap\ActiveField`, `yii\bootstrap5\ActiveField`, `humhub\modules\ui\form\widgets\ActiveField`: use `humhub\widgets\form\ActiveField` instead
- `humhub\modules\ui\form\widgets\SortOrderField` use `humhub\widgets\form\SortOrderField` instead
- `humhub\modules\ui\form\widgets\ContentHiddenCheckbox` use `humhub\widgets\form\ContentHiddenCheckbox` instead
- `humhub\modules\ui\form\widgets\ContentVisibilitySelect` use `humhub\widgets\form\ContentVisibilitySelect` instead
- `humhub\modules\ui\form\widgets\FormTabs` use `humhub\widgets\bootstrap\FormTabs` instead
- `humhub\widgets\ColorPickerField` and `kartik\widgets\ColorInput` use `->colorInput()` instead

### Input groups

Remove `<span class="input-group-btn">` button wrapper inside `<div class="input-group">`.

Before:

```html
<div class="input-group">
  <span class="input-group-btn">
    <button class="btn btn-default">My action</button>
  </span>
</div>
```

Now:

```html
<div class="input-group">
    <button class="btn btn-light">My action</button>
</div>
```

### Error messages with RichTextField input widget

When using the `RichTextField` input widget AND setting the `form` attribute, as the active input is displayed as a nested input, the input elements mustn't be displayed twice.
Previously, the error field was displayed by the parent input.
But Bootstrap 5 requires the error field to be at the same level to the displayed input.

So now, the error HTML element (`invalid-feedback`) is included in the widget.
Which means it needs to be removed in the parent input (by specifying the template) to prevent displaying it twice.

Example:

```php
<?= $form->field($model, 'attribute', ['template' => "{label}\n{input}"])->widget(RichTextField::class, [
    'form' => $form,
]) ?>
```


## Dropdown, Navs & tabs

### Dropdown

- Search for `dropdown-menu` in the code and add a `dropdown-header` class to header items, and a `dropdown-item` class to all link items (usually `a` and `button` tags ; [see documentation with example](https://getbootstrap.com/docs/5.3/components/dropdowns/#examples)).
- Search for `divider` classes (search regex expression for HTML tags: `<\w+\s+[^>]*class\s*=\s*["'](?:[^"']*\s)?divider(?:\s[^"']*)?["'][^>]*>`), replace with `dropdown-divider`, and move them to a `hr` child tag of `li`.
- Move the tags with `dropdown-header` class to a child tag of `li` (usually in a `h6` tag)
- `dropdown-menu-left` -> `dropdown-menu-start`
- `dropdown-menu-right` -> `dropdown-menu-end`
- `dropdown-menu float-start` -> `dropdown-menu dropdown-menu-start`
- `dropdown-menu float-end` -> `dropdown-menu dropdown-menu-end`
- Remove `<span class="caret"></span>` and `<b class="caret"></b>` in dropdown buttons (as there are already added by Bootstrap 5 via the `:after` pseudo-element)

Before:

```html
<li class="dropdown">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        Dropdown button
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
        <li><h6>Dropdown header</h6></li>
        <li role="separator" class="divider"></li>
        <li><a href="#">Action</a></li>
    </ul>
</li>
```

Now:

```html
<div class="dropdown">
    <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        Dropdown button
    </button>
    <ul class="dropdown-menu">
        <li><h6 class="dropdown-header">Dropdown header</h6></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="#">Action</a></li>
    </ul>
</li>
```

#### Javascript

The jQuery `.dropdown()` method is removed. Use BS5 vanilla JS API instead. Example for a close button:

```js
$('#hideDropdownButton').on('click', function () {
    const $dropdown = $('#my-dropdown'); // which has the `dropdown` class
    const bsDropdown = new bootstrap.Dropdown(dropdownElement); // Bootstrap's API
    bsDropdown.hide(); // https://getbootstrap.com/docs/5.3/components/dropdowns/#methods
});
```


## Navs & tabs

Make sure the required classes `nav-item` and `nav-link` exist in HTML tags about nav & tabs ([see documentation with examples](https://getbootstrap.com/docs/5.3/components/navs-tabs/)).
Search regex expression for HTML tags: `<\w+\s+[^>]*class\s*=\s*["'](?:[^"']*\s)?nav(?:\s[^"']*)?["'][^>]*>`.

The `active` class must be added to the `nav-link` element (and not the `nav-item`).

Example:

```html
<ul class="nav">
    <li class="nav-item">
        <a class="nav-link active" href="#">Link</a>
    </li>
    ...
</ul>
```

So in CSS, `li.active a` must be replaced by `li a.active`.

### Tabs with Dropdown

Example:

```html
<ul class="nav nav-tabs">
    <li class="nav-item">
        <a class="nav-link" href="#">Link</a>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Dropdown</a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Action</a></li>
            <li><hr class="dropdown-divider"></li>
        </ul>
    </li>
</ul>
```


## Replacements in HTML attributes

These replacements must be done in PHP, SCSS (formerly LESS) and JS files.

### General classes replacements

- `img-responsive` -> `img-fluid` (use the `humhub\modules\ui\widgets\BaseImage` widget when possible)
- `alert-default` -> `alert-light` or  `alert-secondary` (use the `humhub\widgets\bootstrap\Alert` widget when possible)
- `btn-xs` -> `btn-sm` (use the `humhub\widgets\bootstrap\Button` widget when possible)
- `btn-default` -> `btn-light` (the new  `btn-secondary` can also be used, but it will be a darker gray)
- `pull-left` -> `float-start`
- `pull-right` -> `float-end`
- `center-block` -> `mx-auto` (image, inline or inline-block elements:  `d-block mx-auto`)
- `text-left` -> `text-start`
- `text-right` -> `text-end`
- `btn-group-xs` -> `btn-group-sm`
- `media-object img-rounded` -> `rounded`
- `img-rounded` -> `rounded`
- `data-toggle` -> `data-bs-toggle`
- `data-target` -> `data-bs-target`
- `data-dismiss` -> `data-bs-dismiss`
- `no-space` -> `m-0 p-0`
- `align-center` -> `text-center` or  `d-flex justify-content-center`
- `input-group-addon` -> `input-group-text` (or  `input-group-prepend` or  `input-group-append`)
- `form-group` -> `mb-3`
- `well` (search regex expression for HTML tags: `<\w+\s+[^>]*class\s*=\s*["'](?:[^"']*\s)?well(?:\s[^"']*)?["'][^>]*>`) -> `bg-light p-3` for a simple inset container or `card` (with a  `card-body` child element)
- `has-error`,  `has-warning`, and  `has-success` -> `is-invalid` or  `is-valid`, but the new classes are now append to the input instead of the previous  `form-group` (the input is a child of the form group)
- `help-block help-block-error` -> `invalid-feedback`
- `help-block` -> `text-body-secondary` or  `form-text` if in a form
- Remove  `jumbotron` class

### Columns with breakpoints

Bootstrap 3:
- `xs`: < 768px
- `sm`: ≥ 768px
- `md`: ≥ 992px
- `lg`: ≥ 1200px

Bootstrap 5:
- `xs`: removed (default)
- `sm`: ≥ 576px (new)
- `md`: ≥ 768px
- `lg`: ≥ 992px
- `xl`: ≥ 1200px
- `xxl`: ≥ 1400px (new)

So replacements are (to be done in this order):
- `col-lg-` -> `col-xl-`
- `col-md-` -> `col-lg-`
- `col-sm-` -> `col-md-`
- `col-xs-` -> `col- `

Make sure:
- the parent element has the `row` class
- the parent of parent the `container` class

[See documentation](https://getbootstrap.com/docs/5.3/layout/columns/)

### Hidden/Visible elements

Use the new `d-none` class instead of the `display: none;` style (except for email views).

In the following class replacements, you can also use `inline`, `flex`, etc. instead of `block` (depending on the desired display mode).
E.g., `d-sm-inline` or `d-sm-flex` instead of `d-sm-block`.

In Bootstrap 3, the class applies to the designed screen size only, whereas in Bootstrap 5, the class applies to the designated screen size and larger.
And `visible` will hide the element for other screen sizes, whereas in Bootstrap 5, you need to add `d-none` to hide for other screen sizes.

Remplacement examples (must be adapted to the specific situation):
- `hidden-xs` -> `d-none d-sm-block` or `d-none d-sm-inline` or `d-none d-sm-flex` (depending on the desired display mode)
- `hidden-sm` (hide on small screens only) → `d-sm-none d-md-block` (hide on small screens, but show on medium or above ; idem, replace `block` with `inline` or `flex`)
- `hidden-md` (hide on medium screens only) → `d-md-none d-lg-block` (hide on medium screens, but show on large or above)
- `hidden-lg` (hide on large screens) → `d-lg-none` (hide on large screens and above, including extra large screens which doesn't exist in Bootstrap 3)
- `hidden` (search regex expression for HTML tags: `<\w+\s+[^>]*class\s*=\s*["'](?:[^"']*\s)?hidden(?:\s[^"']*)?["'][^>]*>` ; search also in JS for strings such as  `Class('hidden')`, `Class("hidden")`, `Class' => 'hidden'`) -> `d-none` and others
- `visible-xs` (hide on small screens and above) → `d-block d-sm-none` or `d-sm-none` if the element is visible by default
- `visible-sm` (visible on small screens only) → `d-none d-sm-block d-md-none` (hide on all screens except small screens)
- `visible-xs visible-sm` -> `d-md-none` (hide on large screens of above)
- `visible-md` → `d-none d-md-block d-lg-none` (show on medium screens only)
- `visible-lg` → `d-none d-lg-block` (show on large screens or above)
- `visible-md visible-lg` -> `d-none d-md-block` (show on medium screens of above)
- `visible` (search regex expression for HTML tags: `<\w+\s+[^>]*class\s*=\s*["'](?:[^"']*\s)?visible(?:\s[^"']*)?["'][^>]*>` ; search also in JS for strings such as  `Class('visible')`, `Class("visible")` and `Class' => 'visible'`) → `d-block`

#### JavaScript with `d-none`

In Bootstrap 5 CSS, the `d-flex` class is set to `flex !important`, and the `d-none` class to `display: none !important;`.

So, the jQuery `hide()` and `show()` functions won't work anymore, because of the `!important`.

Replacements to do on these elements:

- `.hide()` -> `.addClass('d-none')`
- `.show()` -> `.removeClass('d-none')`
- `.toggle()` -> `.toggleClass('d-none')`
- `.fadeIn()` -> `.removeClass('d-none')`
- `.fadeOut()` -> `.addClass('d-none')`
- `.fadeToggle()` -> `.toggleClass('d-none')`
- `.slideDown()` -> `.removeClass('d-none')`
- `.slideUp()` -> `.addClass('d-none')`
- `.slideToggle()` -> `.toggleClass('d-none')` 

### Spinners

Search for `sk-`.

Before:

```html
<div class="sk-spinner sk-spinner-three-bounce">
    <div class="sk-bounce1"></div>
    <div class="sk-bounce2"></div>
    <div class="sk-bounce3"></div>
</div>
```

After, for a button:

```html
<span class="spinner-border spinner-border-sm"></span>
```

After, in a container:

```html
<div class="text-center">
    <div class="spinner-border" role="status">
        <span class="visually-hidden"><?= Yii::t('base', 'Loading...') ?></span>
    </div>
</div>
```

If wrapped in an HTML element having `loader` (search for the `<\w+\s+[^>]*class\s*=\s*["'](?:[^"']*\s)?loader(?:\s[^"']*)?["'][^>]*>` regex expression) or `humhub-ui-loader` classes, replace the hole HTML code with the `LoaderWidget` widget.

[See documentation](https://getbootstrap.com/docs/5.3/components/spinners) for more options and examples.

### Panel

Should be replaced with cards.

TODO in core and to document here.

### Label & Badge

Search for all `label` classes (`label label-` and the regex expression `<\w+\s+[^>]*class\s*=\s*["'](?:[^"']*\s)?label(?:\s[^"']*)?["'][^>]*>`) and use the new `humhub\widgets\bootstrap\Badge` widget instead

Use the `humhub\widgets\bootstrap\Badge` widget when possible.

Replacements:
- `badge-default` -> `text-bg-light` or `text-bg-secondary`
- `badge-primary` -> `text-bg-primary`
- `badge-danger` -> `text-bg-danger`
- `badge-warning` -> `text-bg-warning`
- `badge-info` -> `text-bg-info`
- `badge-success` -> `text-bg-success`

Doc: https://getbootstrap.com/docs/5.3/components/badge/

### Media

- Search for `media-list` and remove the HTML element, or, to keep a similar style, use the class `hh-list` and replace the `ul` tag with a `div`. E.g. `<ul class="media-list">` -> `<div class="hh-list">`
- Inside, replace `li` tags with `div` or `a` tags. E.g. `<li class="media">` -> `<div class="d-flex">`
- Search for `media` classes (search regex expression for HTML tags: `<\w+\s+[^>]*class\s*=\s*["'](?:[^"']*\s)?media(?:\s[^"']*)?["'][^>]*>`) and replace with `d-flex`
- Search for `img-space` classes and surround the `UserImage` and `SpaceImage` with the `img-profile-space` (see example in `protected/humhub/modules/content/widgets/views/wallEntry.php`)
- `media-heading` -> `mt-0` (removes the top margin, keeping it close to the top of the content area) ; the related HTML tag can be replaced with `h5` or `h4`
- `media-body` -> `flex-grow-1`
- `media-left` -> `flex-shrink-0 me-2`
- `media-right` -> `flex-shrink-0 ms-2 order-last`
- `media-object` -> `flex-shrink-0` (if on an image, encapsulate the image in a `div` tag with `flex-shrink-0` class)
- Remove `float-start` (or `pull-left`) class for images inside a `<div class="flex-shrink-0">`

Doc: https://getbootstrap.com/docs/5.3/utilities/flex/#media-object


### clearfix

The `.clearfix` class works differently in Bootstrap 5.
Now, the `clear: both;` is done with a `:after` pseudo-element.

Before:

```html
<div>
    <div class="pull-right">...</div>
    <div class="clearfix"></div>
</div>
```

After:

```html
<div class="clearfix">
    <div class="float-end">...</div>
</div>
```

## Themes and Modules: LESS is replaced with SCSS

LESS format is not supported anymore.
Use SCSS instead.

Doc: https://getbootstrap.com/docs/5.3/customize/sass/

### Convert LESS to SCSS

Rename `less` folder to `scss` and rename all `.less` files to `.scss`
Prefix all SCSS files with `_` except the `build.scss` and `variables.scss` files.
E.g.: `less/theme.less` -> `scss/_theme.scss`
Linux command: `for file in *.less; do mv "$file" "_${file%.less}.scss"; done` and remove the `_` for the `build.scss` file

You can use the following tool to convert LESS to SCSS: https://less2scss.awk5.com/
However, you need to check the output manually, mainly functions and syntaxes such as:
- `color: fade(@color, 20%);` -> `color: mix(white, $primary, 80%);`
- `transition:`: remove the `@include` added after the conversion

An AI such as https://claude.ai/ might be more powerful to convert, but still requires manual checks.

### SCSS and CSS variables

#### Changes

Deprecated SCSS variables:
- `$default`: use `$light` instead
- `$link`: use `$link-color` instead

New SCSS variables:
- `$secondary`
- `$light`
- `$dark`

#### CSS variable prefixes

Use the new variables starting with `--bs-` for Bootstrap variables, and `--hh-` for HumHub variables.
E.g.: `color: $primary` -> `color: var(--bs-primary)`
Full deprecation list in `static/scss/variables.scss`.

In modules or custom themes, if you need new variables, prefix them with `--hh-xx-` where `xx` is the first letters of your module or theme ID. E.g. `my-module` will use `hh-mm-`.

#### Use CSS variables instead of SCSS variables

In all SCSS files (except in SASS functions), replace all SCSS variables with CSS variables, when available (see list in `variables.scss`), except the one used in SCSS function (e.g. `lighten($primary, 5%)`). You can use regex:
- search: `\$([a-zA-Z0-9-_]+)`
- replace: `var(--bs-$1)` (mainly for base colors such as `$primary`) or `var(--hh-$1)`

#### Root vs component variables

**Root variables** are global variables that can be used in any component.
They are stored in this file: `_root.scss`
See https://getbootstrap.com/docs/5.3/customize/css-variables/#root-variables

**Component variables** only apply to the HTML elements having the related class (e.g. `.badge` for [Badge CSS variables](https://getbootstrap.com/docs/5.3/components/badge/#variables)), and HTML elements inside of it.

Their values can be overwritten in the component related SCSS file (e.g. `_badge.scss`). Example:
```scss
.badge {
    --bs-badge-padding-x: 0.8em;
}
```

Full list of Bootstrap CSS variables here: https://github.com/twbs/bootstrap/tree/main/scss

#### Global colors

Doc: https://getbootstrap.com/docs/5.3/customize/color/

Availability:
- SCSS variables
- root CSS variables
- CSS classes (utility classes)

##### Theme colors

`primary`, etc.

3 Variations:
- `primary-bg-subtle`
- `primary-border-subtle`
- `primary-text-emphasis`

##### All colors

`blue`, etc.

Variations:
- `blue-100` (lighter)
- ...
- `blue-500` (= `blue`)
- ...
- `blue-900` (darker)

##### Other colors

`body-color`, etc.

#### Component colors

Example for alerts: https://getbootstrap.com/docs/5.3/components/alerts/#variables

Availability:
- CSS variables: `--bs-alert-bg`, etc
- CSS classes: `.alert-primary`, etc

In components:
- CSS variables only apply to HTML elements having a certain class. E.g.: `.alert`
- there are no color SCSS variables
- but CSS variable values are usually defined from global SCSS variables


### Breakpoints

Search for `@media` and replace with SCSS functions:

```scss
// X-Small devices (portrait phones and up)
// No media query necessary for xs breakpoint as it's effectively `@media (min-width: 0) { ... }`

// Small devices (landscape phones and up)
@include media-breakpoint-up(sm) { ... } // @media (min-width: 576px) { ... }

// Medium devices (tablets and up)
@include media-breakpoint-up(md) { ... } // @media (min-width: 768px) { ... }

// Large devices (desktops and up)
@include media-breakpoint-up(lg) { ... } // @media (min-width: 992px) { ... }

// X-Large devices (large desktops and up)
@include media-breakpoint-up(xl) { ... } // @media (min-width: 1200px) { ... }

// XX-Large devices (larger desktops)
@include media-breakpoint-up(xxl) { ... } // @media (min-width: 1400px) { ... }
```

It is also possible to use `max-width` (should be occasionally used) using `media-breakpoint-down`. E.g.:

```scss
// `sm` applies to x-small devices (portrait phones and down)
@include media-breakpoint-down(sm) { ... } // @media (max-width: 575.98px) { ... }

// `md` applies to small devices (landscape phones and down)
@include media-breakpoint-down(md) { ... } // @media (max-width: 767.98px) { ... }

// etc...
```

In modules, you will have to import the Boostrap breakpoints SCSS functions:
```scss
@import "../../../../vendor/bower-asset/bootstrap/scss/functions";
@import "../../../../vendor/bower-asset/bootstrap/scss/variables";
@import "../../../../vendor/bower-asset/bootstrap/scss/mixins/breakpoints";
```

Doc: https://getbootstrap.com/docs/5.3/layout/breakpoints

### Select2 stylesheet

`static/css/select2Theme` folder has been removed, and the SCSS file moved and renamed to `static/scss/_select2.scss`

### Themes

Many styles have been refactored. Please review all your overwritten CSS selectors and values.

### Structure

All theme folders must be moved to a new `resources` folder, except the `scss` and `views` folders.

Structure example:
- resources
- resources/css (empty because the CSS files are generated by the compiler, so it can be added to the `.gitignore` file)
- resources/js/humhub.my-theme.js
- views
- scss/build.scss
- scss/_mixins.scss
- scss/_root.scss
- scss/_theme.scss
- scss/variables.scss

#### Build file

The `build.scss` file mustn't import the parent theme files anymore, as it is automatically done by the new compiler.

Take example with the `HumHub` community theme.

#### Compiler

Grunt compiler has been removed.

Instead, compile your theme with the web browser, in Administration -> Settings -> Appearance, just by saving the form.

If you use the "Updater" module to update HumHub core, you don't need to recompile your custom theme CSS anymore, as it will be done automatically.

But without this module, you will have to save this form after each HumHub core upgrade, to rebuild the CSS with the new changes.

#### Overwritten view files

Most of the views have been refactored to use the new Bootstrap 5 HTML tags and classes.

Please review all overwritten view files. See [Migration: Identify Template Changes](https://community.humhub.com/content/perma?id=237199) wiki for more information.

The most important change concerns the `protected/humhub/views/layouts/main.php` file, which has been refactored with bs5 flex logic (instead of floating right elements).


## Documentation

- BS3 to BS4: https://getbootstrap.com/docs/4.6/migration/ and https://www.yiiframework.com/wiki/2556/yii2-upgrading-to-bootstrap-4
- BS4 to BS5: https://getbootstrap.com/docs/5.3/migration/ and https://github.com/humhub/yii2-bootstrap5/blob/master/docs/guide/migrating-yii2-bootstrap.md
- BS3 to BS5: https://nodebb.org/blog/nodebb-specific-bootstrap-3-to-5-migration-guide/
