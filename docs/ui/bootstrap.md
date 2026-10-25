# Styling with Bootstrap 5

> Applies to every component in this styleguide · Core: `protected/humhub/resources/scss/`,
> compiled by `humhub\helpers\ThemeHelper` together with Bootstrap 5

The designer's prototype is not built on Bootstrap. Its CSS uses fixed pixel values, its own
class names and its own versions of buttons, tabs, selects and dropdowns. Core is built on
**Bootstrap 5**. So the design is **translated** into Bootstrap 5, never copied: the prototype
tells us what a component looks like, and this page tells us how that look is built in core.

This applies to everything new, and to the components that came with 1.20 (`PageToolbar`,
`FilterBar`, the entity browser, the item browser). Their SCSS is still close to the prototype's
CSS. Since 1.20 is not released yet, they move to these rules without backwards compatibility
concerns (see [Migrating the 1.20 classes](#migrating-the-120-classes)).

## Rules

### 1. Build on the Bootstrap component

When Bootstrap 5 has a component for the job, the design is built **on** it: same markup, same
classes, the look set through Bootstrap's Sass variables. A new class is only added for what
Bootstrap doesn't have.

| Design | Bootstrap 5 base |
|---|---|
| Buttons, icon buttons, choice rows | `.btn`, `.btn-check`, `.btn-group` |
| Pill Tabs, section menu | `.nav`, `.nav-pills`, `.nav-link` |
| Context menu, select drawer, picker suggestions | `.dropdown`, `.dropdown-menu`, `.dropdown-item` |
| Dialog | `.modal` |
| Accordion | `.accordion` |
| Content card | `.card`, `.card-header`, `.card-body` |
| Form fields, select | `.form-control`, `.form-select`, `.form-label`, `.form-text`, `.invalid-feedback` |
| Switch, checkbox, radio | `.form-check`, `.form-switch`, `.form-check-input` |
| Field with an action (show password, copy) | `.input-group` |
| Badges, chips, counts | `.badge` |
| Toast | `.toast`, `.toast-container` |
| Tooltip | `.tooltip` (Bootstrap's Tooltip JS) |
| Hover card | `.popover` (Bootstrap's Popover JS) |
| Skeletons | `.placeholder`, `.placeholder-glow` / `.placeholder-wave` |
| Spinner | `.spinner-border` |
| Progress (multi-step dialog) | `.progress`, `.progress-stacked` |
| Path bar | `.breadcrumb` |
| Data table | `.table` |
| File rows | `.list-group` |
| Notice | `.alert` |
| Menu sheet and widget drawer on phones | `.offcanvas.offcanvas-bottom` |
| Top bar | `.navbar` |

The full mapping per component is in [Components and their classes](#components-and-their-classes).

### 2. Name classes the Bootstrap way

New classes follow Bootstrap's naming, so they read like the classes around them (`.card`,
`.card-header`, `.nav-pills`, `.btn-accent`):

| Kind | Pattern | Example |
|---|---|---|
| Component | `.<component>` | `.page-header`, `.filter-bar`, `.entity-card` |
| Part | `.<component>-<part>` | `.page-header-title`, `.page-header-actions`, `.filter-bar-item` |
| Variant or modifier | an extra class `.<component>-<variant>` | `.btn-ghost`, `.nav-pills-secondary`, `.badge-count` |
| State | Bootstrap's state classes, otherwise `.is-<state>` | `.active`, `.show`, `.disabled`, `.is-invalid`, `.is-open`, `.is-loading` |
| JS hook | a `data-*` attribute, never a class | `data-ui-tabs`, `data-filter-bar-keep` |

- No `c-` prefix and no BEM (`__part`, `--modifier`).
- A modifier always comes **with** its component class: `.btn.btn-ghost`, `.nav.nav-pills.nav-pills-secondary`.
- Never reuse a Bootstrap class name for something else. Before adding a class, check that
  Bootstrap 5 (and `_bootstrap3.scss`, the compatibility layer) doesn't define it.

### 3. Values come from Sass variables

- Every value a component uses is a **Sass variable with `!default`**, named
  `$<component>-<property>` (`$page-header-padding`, `$page-header-border-radius`), declared at
  the **head of the component's own file**. `variables.scss` is compiled before Bootstrap's
  variables, so a value derived from `$spacer` or `$border-radius-lg` can't live there. Themes
  override them in their `variables.scss` like any Bootstrap variable (`!default` keeps theirs).
- Variables are **derived from Bootstrap's and HumHub's existing values**: `$spacer` and its
  steps, `$border-radius`, `$border-radius-lg`, `$box-shadow-sm`, `$font-weight-semibold`,
  `$h*-font-size`, `$body-bg`, `$border-color`.
- **When in doubt, stay with Bootstrap and the current HumHub design.** The prototype's exact
  pixel values (8px grid, 6px radii, its shadows) are not a reason to deviate. A component takes
  the **nearest existing value**, e.g. `$spacer * .5` for the prototype's 8px (7px at
  `$font-size-root` 14px). Only structure, behaviour and the parts of a component come from the
  design. Visual details that differ are collected as open questions and decided for the whole
  design, not per component.
- **No pixel values in rules.** Everything goes through the variables. Layout widths that define
  behaviour (fold widths, minimum column widths) are the exception: they are Sass variables in px,
  like Bootstrap's own `$grid-breakpoints`.
- Bootstrap components are customised **through their Sass variables** (`$nav-pills-*`,
  `$dropdown-*`, `$modal-*`, `$input-*`). Only a customisation scoped to one selector sets
  `--bs-<component>-*` in a component file (as `.btn-ghost` does). This is the rule from the head
  of `variables.scss`.
- Global Bootstrap and HumHub values (e.g. `$btn-border-radius`, today 3px against the
  prototype's 6px) are **not** changed per component. Whether and when they move is decided for
  the whole design (see Open questions).

### 4. Each component declares its own CSS variables

As Bootstrap 5 does with `--bs-card-*`, a component declares local CSS variables on its root,
filled from the Sass variables. Its rules only use these variables:

```scss
.page-header {
    --hh-page-header-padding-y: #{$page-header-padding-y};
    --hh-page-header-bg: #{$page-header-bg};
    // …

    padding: var(--hh-page-header-padding-y) var(--hh-page-header-padding-x);
    background: var(--hh-page-header-bg);
}
```

- The prefix is `--hh-`, so HumHub variables never collide with Bootstrap's `--bs-`.
- A theme can override a value with Sass (all pages) or with the CSS variable (one place, or at
  runtime).
- Colours point to variables that switch with the colour mode: `var(--bs-body-bg)`,
  `var(--bs-border-color)`, `var(--bs-accent)`, `var(--hh-background-color-main)`,
  `var(--hh-text-color-highlight)`.
- No parallel token sets for things Bootstrap already has. A field is styled by `$input-*`, not
  by a `--hh-field-*` set.

### 5. Variants come from Bootstrap's maps and mixins

- Colour variants are generated from `$theme-colors` (which contains `accent`, see `maps.scss`)
  and its subtle maps (`$theme-colors-bg-subtle`, `-text`, `-border-subtle`), in a loop, like
  Bootstrap's own `.btn-*`, `.alert-*` and `.text-bg-*`.
- Buttons use `button-variant()` / `button-outline-variant()`. A variant without a fill (ghost)
  is defined the way Bootstrap defines `.btn-link`: by setting the class's `--bs-btn-*`
  variables.
- Transitions use `@include transition(…)`. It respects `$enable-transitions` and
  `prefers-reduced-motion`, so no component needs its own reduced-motion block.
- Shadows use `@include box-shadow(…)` (respects `$enable-shadows`).

### 6. Dark mode

- A colour that differs in dark mode gets a `$<component>-<property>-dark` variable next to the
  light one (the pattern `variables.scss` already uses, e.g. `$input-bg-dark`).
- The component sets the dark value inside `@include color-mode(dark) { … }`, on its own local
  CSS variable. Most components need nothing here, because their colours already point to
  variables that switch.

### 7. One file per component

- Each component has its own `_<component>.scss` in `resources/scss/`, imported in `build.scss`
  behind a `$prev-<component>` switch, so a theme can turn it off:

  ```scss
  $prev-page-header: false !default;
  @import "page-header";
  ```

- A component that extends a Bootstrap component goes into that component's file: icon buttons
  and ghost into `_button.scss`, pill variants into `_nav.scss`, row styles into `_dropdown.scss`.
- No "kits": a file holds one component. Shared field styling belongs to `_form.scss`, not to the
  first component that needed it.

### 8. Utilities

- Bootstrap utility classes (`d-flex`, `gap-2`, `ms-auto`, `w-100`, `visually-hidden`) are fine
  for layout in views.
- A component's **look** never depends on utilities. A chip is `.badge.badge-success`, not
  `.badge.text-bg-success` plus spacing utilities, so the look can change in one place.

### 9. Responsiveness

- Components fold by **their own width**, with container queries (`container-type: inline-size`),
  as the [Principles](principles.md#layout-and-responsiveness) require. Breakpoint widths are Sass
  variables (`$filter-bar-fold-width`).
- Bootstrap's viewport breakpoints (`media-breakpoint-up()`) are only for the app shell (top bar,
  mobile navigation).

## Components and their classes

The class names each component gets. Details are on the component pages.

| Component | Bootstrap 5 base | Own classes |
|---|---|---|
| [Page Header](page-header.md) | (own; laid out like `.card`) | `.page-header`, `-bar`, `-back`, `-title`, `-actions`, `-body` |
| [Content Card](card.md) | `.card`, `.card-header`, `.card-title`, `.card-body`, `.card-footer` | `.content-card`, `.card-header-actions` |
| [Pill Tabs](tabs.md) | `.nav.nav-pills` | `.nav-pills-secondary`, `.nav-scroller` |
| [Master / Detail](master-detail.md) | section menu on `.nav.flex-column` in a `.card` | `.pane-split`, `-slim`, `-end`, `-side`, `-main`; `.nav-section` |
| [Top Bar](top-bar.md) | `.navbar` (`.navbar.topbar` today), `.dropdown-menu` | `.topbar-drawer` |
| [Space & Profile Header](container-header.md) | tabs on `.nav-pills`, ⋮ on `.dropdown` | `.container-header`, `-compact`, `-cover`, `-image`, `-counters`, `-title`, `-subtitle`, `-actions`, `-nav` |
| [Mobile Navigation](mobile-navigation.md) | sheet and drawer on `.offcanvas.offcanvas-bottom` | `.mobile-nav`, `-item`, `-sheet`; `.mobile-widgets` |
| [Sidebar](sidebar.md) (Enterprise theme) | `.nav.flex-column`, `.collapse` | `.sidebar`, `.sidebar-group` |
| [Buttons](buttons.md) | `.btn`, `.btn-check` | `.btn-icon-only`, `.btn-ghost`, `.btn-ghost-danger`, `.btn-reveal` |
| [Badges & Status](badges.md) | `.badge` | `.badge-<tone>`, `.badge-tag`, `.badge-count`, `.badge-dot`, `-waiting`, `-unsaved` |
| [Accordion](accordion.md) | `.accordion` | `.accordion-icon`, `-title`, `-subtitle`, `-footer`, `-header-actions`, `-dot`, `-compact` |
| [Dialog](dialog.md) | `.modal`, `.modal-dialog-scrollable`, `.progress-stacked` | `.modal-body-fades`, `.modal-footer-fill`, `.modal-steps` |
| [Context Menu](context-menu.md) | `.dropdown-menu` | `.dropdown-item-icon`, `.dropdown-item-danger` |
| [Form Fields](form-fields.md) | `.form-control`, `.form-select`, `.form-text`, `.invalid-feedback`, `.input-group` | `.form-pair` |
| [Select & Pickers](select-pickers.md) | `.form-select` (native), `.form-control` (custom button and picker field), `.dropdown-menu` / `.dropdown-item` (listbox) | `.select-menu`, `-button`, `-drawer`, `-option`; `.picker`, `-field`, `-chip`, `-input`; `.picker-filter` |
| [Date Picker](date-picker.md) | `.input-group` > `.form-control`, `.dropdown-menu` | `.datepicker`, `-header`, `-grid`, `-day`, `-time` |
| [Switch, Checkbox & Radio](choice-controls.md) | `.form-check`, `.form-switch` | `.form-switch-row`, `.form-check-card` |
| [File Upload](file-upload.md) | `.list-group`, `.invalid-feedback` | `.dropzone`, `.file-row`, `-icon`, `-name`, `-meta`, `-progress`; `.image-setting` |
| [Toast](toast.md) | `.toast`, `.toast-container`, `.btn-close` | `.toast-<tone>`, `.toast-icon`, `-message`, `-details`, `-details-toggle`, `-close`; later `.toast-group` |
| [Tooltip](tooltip.md) | `.tooltip` | — |
| [Hover Card](hover-card.md) | `.popover` | `.hover-card`, `-cover`, `-image`, `-title`, `-subtitle`, `-actions` |
| [Loading, Empty & Error](loading-empty-error.md) | `.placeholder`, `.spinner-border` | `.empty-state`, `-error`, `-icon`, `-title`, `-text`; `.is-reloading` |
| [Filter Bar](filter-bar.md) | fields on `.form-control`, the toggle and clear on `.btn`, the count on `.badge` | `.filter-bar`, `-body`, `-item`, `-toggle`, `-count`, `-panel`, `-clear`; `.search-field`; `.filter-tags`, `.filter-check` |
| [Data Table](data-table.md) | `.card`, `.table`, `.table-active`, `.pagination` | `.data-table`, `-main`, `-sort`, `-row-details`, `-bulk`; `.card-header-actions` |
| [Entity Browser](entity-browser.md) | notice on `.alert`, "Show more" on `.btn` | `.entity-browser`, `-notice`; `.entity-grid`, `-cells`, `-cell`, `-cell-skeleton`, `-message`, `-error`, `-more` |
| [Entity Card](entity-card.md) | `.card`, skeleton on `.placeholder` | `.entity-card`, `-cover`, `-avatar`, `-image`, `-pill`, `-header`, `-title`, `-body`, `-tags`, `-footer`, `-skeleton`; `.people-card`, `.space-card`, `.module-card` |
| [Item Browser](item-browser.md) | path bar on `.breadcrumb`, view switch on `.btn-check`, checkbox `.form-check-input`, skeleton `.placeholder` | `.path-bar`, `.selection-menu`, `.tile-grid`, `.tile`, `.drop-zone`, `.progress-frame` |
| [Settings Page](settings-page.md) | `.card`, `.accordion`, `.alert` | `.settings-page`, `.settings-control` |

The content card is Bootstrap's `.card`. It replaces the Bootstrap 3 `.panel` / `.panel-heading`
that core still renders through `_bootstrap3.scss` (see [Content Card](card.md)).

## Migrating the 1.20 classes

The 1.20 components use `c-` BEM classes. They move to the names above when the component is
reworked. Without a release in between, no aliases are kept. Done so far: the page header, the
icon button and ghost, the split of `_page-toolbar.scss`, the filter bar's class names, the
content card, the entity card and the item browser.

| 1.20 | Becomes |
|---|---|
| `_page-toolbar.scss` | `_page-header.scss`, `_filter-bar.scss`; icon button → `_button.scss`; field and select styles → `_form.scss` |
| `.c-page-toolbar`, `__header`, `__title`, `__actions` | `.page-header`, `-bar`, `-title`, `-actions` |
| `.c-icon-button`, `--ghost` | `.btn-icon-only`, `.btn-ghost` |
| `--hh-field-*`, `--hh-surface-shadow` | `.form-control` (`$input-*`), the listbox's shadow from `.dropdown-menu` |
| `.c-filter-bar-container`, `.c-filter-bar`, `__item`, … | `.filter-bar`, `.filter-bar-body`, `.filter-bar-item`, … |
| `.c-search-field…` | `.search-field…` |
| `.c-select…` | `.select-menu…`; listbox on `.dropdown-menu` / `.dropdown-item` (`.is-active` → `.is-focused`, `.is-selected` → `.active`) |
| `.c-picker…`, `.c-picker-filter…`, `.c-user-filter…` | `.picker…`, `.picker-filter…`, `.user-filter…` |
| `.c-filter-tags…`, `.c-filter-check` | `.filter-tags…`, `.filter-check`; later `.btn-check` toggles and `.form-check` |
| `.c-entity-card…`, `.c-people-card…`, `.c-space-card…`, `.c-module-card…` | `.card.entity-card` with `.entity-card-…`, `.people-card…`, `.space-card…`, `.module-card…` |
| `_item-browser.scss` (`.c-path-bar`, `.c-tile-grid`, `.c-view-switch`, …) | `.path-bar` + `.breadcrumb`, `.tile-grid` / `.tile`, `.btn-group` + `.btn-check`, … |
| Marketplace: `.c-mp-notice…`, `.c-mp-dialog…`, `.c-mp-settings…`, `.c-licence-…`, `.c-install-dialog…` | `.alert.marketplace-alert`, `.nav-pills-secondary`, `.list-group.licence-list`, `.progress`; parts `marketplace-dialog-…`, `marketplace-settings-…`, `install-dialog-…` |
| Notification settings: `.c-notification-settings__…`, own profile radio cards, own category rows and channel pills | `.notification-settings-…` in `.card.content-card`; profiles on `.form-check-cards` / `.form-check-card`; categories on `.accordion` (`.accordion-icon`, `-title`, `-subtitle`, `.accordion-button-static`); channels on `.badge.badge-accent` / `.badge-neutral`; saving on `.spinner-border-sm` |
| Status bar: `.status-bar-body`, `-content`, `-header`, `-close`, `-details`, `-visible`, `.showMore` (`_user-feedback.scss`) | `.toast-container` > `.toast.toast-<tone>` > `.toast-body`; `.toast-close`, `.toast-details`, `.is-visible` (`_toast.scss`) |
| `CardDirectory.vue`, `CardGrid.vue`, `.c-card-directory…`, `.c-card-grid…` (`_card-directory.scss`); `PeopleDirectory`, `SpaceDirectory` (widgets and islands) | `EntityBrowser.vue`, `EntityGrid.vue`, `.entity-browser…`, `.entity-grid…` (`_entity-browser.scss`); `PeopleBrowser`, `SpacesBrowser` |

## Open questions

1. **Design scale:** the prototype works on an 8px grid, while `$spacer` is 14px (`1rem` at
   `$font-size-root` 14px). Until decided, components use the Bootstrap scale (see rule 3).
   Later options: keep it, or introduce a HumHub spacing map on the 8px grid.
2. **Global alignment:** do Bootstrap's and HumHub's global values (button and badge radius,
   shadows, `$secondary`, dialog widths) move to the prototype's values, and when? Until decided,
   they stay as they are.
