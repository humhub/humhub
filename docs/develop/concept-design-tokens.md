# Design tokens

**Since 1.20.** Every colour of the core SCSS is a token: a CSS variable named after its job. A
component never writes a colour. A colour mode (`[data-bs-theme]`) redefines the semantic tokens
only, and so does a later contrast mode.

Bootstrap 5.3 already has such a semantic layer. HumHub stays as close to Bootstrap as possible:
it uses a Bootstrap variable wherever Bootstrap has the role, in the meaning Bootstrap gives it,
and adds its own only for roles Bootstrap lacks.

Out of scope: spacing, radius, elevation and type tokens; a contrast mode; colour scales per brand
colour (`--primary-50` … `--primary-1000` in the dsv2 prototype).

## Levels

| Level | What | Where | Example |
|---|---|---|---|
| Primitive | Sass variables: the theme API. A theme's `variables.scss` and the Appearance settings set them | `variables.scss` | `$accent`, `$body-bg`, `$hh-page-bg` |
| Semantic | CSS variables naming a job, with the value of the Sass variable of the same name. Redefined per colour mode | Bootstrap's `_root.scss`, core `_root.scss` | `--bs-body-bg`, `--bs-border-color`, `--hh-page-bg` |
| Component | CSS or Sass variables of one component. Refer to semantic tokens, never to a literal colour | the component's partial | `--hh-page-header-bg`, `$nav-pills-active-bg` |

## Names

- **A HumHub variable has the `hh-` prefix, any other one is Bootstrap's.** In Sass `$hh-page-bg`,
  as a token `--hh-page-bg`; `$body-bg` and `--bs-body-bg` are Bootstrap's. A theme's own variables
  have the theme's prefix (`$hh-et-*`, `--hh-et-*`).
- A colour variable has a `-dark` variable for the dark mode, as in Bootstrap (`$body-bg-dark`).
- `$accent` is the exception: HumHub adds it to Bootstrap's theme colours, so it behaves as one
  (`--bs-accent`, `.btn-accent`, `$accent-bg-subtle`).
- Bootstrap's grey scale `$gray-100` … `$gray-900` is not used. It only feeds Bootstrap's semantic
  variables, which `variables.scss` sets. A component variable whose grey shows in the core is set
  to a semantic token, e.g. `$dropdown-header-color: var(--bs-secondary-color)`.

## Semantic tokens

The light values are the current ones. A dark value marked *new* corrects a value of the dark
mode, which no core screen switches on yet. The Sass variable has the token's name; a `$hh-*`
variable becomes the `--hh-*` token in `_root.scss`, a Bootstrap one the `--bs-*` token in
Bootstrap's.

### Text

| Job | Token | Sass | Light | Dark |
|---|---|---|---|---|
| Body text | `--bs-body-color` | `$body-color` | `#555` | `#ddd` |
| Strong text: headings, hovered links | `--bs-emphasis-color` | `$body-emphasis-color` | `#000` | `#fff` |
| Muted text: meta data, help text, captions, placeholders, icons | `--bs-secondary-color` | `$body-secondary-color` | `#7a7a7a` | `#bbb` |
| Faint text: disabled items, hints | `--bs-tertiary-color` | `$body-tertiary-color` | `#aeaeae` | `#999` *new* |
| Text on a background in a theme colour: a primary button, an accent badge, the topbar | `--hh-{color}-contrast` | `$hh-{color}-contrast` | `$color-contrast-dark` or `$color-contrast-light`, whichever reads better (black or white by default) | same |
| Code | `--bs-code-color` | `$code-color` | | |

`--bs-secondary-color` is Bootstrap's muted text (`.text-body-secondary`, formerly `.text-muted`;
`.form-text`, a field's placeholder through `$input-placeholder-color`, table captions, the
breadcrumb). It is unrelated to the theme colour `$secondary`. Muted text in markup uses
`.text-body-secondary`, faint text `.text-body-tertiary`.

Both were Bootstrap's translucent body colour (75 % and 50 %). They become the HumHub greys, which
the `.text-body-secondary` utility used in 62 modules then shows.

### Surfaces

| Job | Token | Sass | Light | Dark |
|---|---|---|---|---|
| Page, behind the surfaces | `--hh-page-bg` | `$hh-page-bg` | `#ededed` | `#000` |
| Surface: panel, card, dropdown, modal, popover, field, table | `--bs-body-bg` | `$body-bg` | `#fff` | `#222` |
| Sunken area in a surface: comments, tabs, filter | `--bs-tertiary-bg` | `$body-tertiary-bg` | `#f9f9f9` | `#333` |
| Fill of a hovered or active item, a neutral badge; a light separator | `--bs-secondary-bg` | `$body-secondary-bg` | `#ededed` | `#393939` |
| Selected or new item, current day | `--bs-accent-bg-subtle` | `$accent-bg-subtle` | `#d3ecf0` | Bootstrap's |
| Lighter selection: a checked choice, a hovered new item | `--hh-accent-bg-soft` | `$hh-accent-bg-soft` | `#f2f9fb` | `#223538` *new* |
| Inline code | `--hh-code-bg` | `$hh-code-bg` | `#dbf5f8` | `$accent-bg-subtle-dark` |
| Marked text: `<mark>`, a matched suggestion | `--bs-highlight-bg` | `$mark-bg` | `#fff4d3` | the warning's subtle background |
| Status: alert, notice | `--bs-{color}-bg-subtle`, `--bs-{color}-text-emphasis`, `--bs-{color}-border-subtle` | Bootstrap's | Bootstrap's | Bootstrap's |

Bootstrap's components take `--bs-body-bg` as their surface. `--bs-body-bg` therefore is the
surface colour, and the `<body>` sets its background to `--hh-page-bg`; no other rule uses
`--hh-page-bg`. A rule that took the page grey for a fill or a light separator takes
`--bs-secondary-bg`, the same `#ededed` in the light mode. The component variables that the core
set to the surface (`$input-bg`, `$dropdown-bg`, `$list-group-bg`, `$table-bg`, `$accordion-bg`,
`$nav-tabs-link-active-bg`) are removed; `$toast-background-color` stays, as Bootstrap's toast is
translucent.

The alerts use Bootstrap's variants (`.alert-success`, …) and their status colours. Their text
reaches a contrast of 7.2–9.4:1 on its fill, formerly 1.9–2.2:1.

`--bs-highlight-bg` is Bootstrap's name for marked text, not for an informative highlight.

### Lines and focus

| Job | Token | Sass | Light | Dark |
|---|---|---|---|---|
| Separator, border of a box | `--bs-border-color` | `$border-color` | `#ddd` | `#3f3f3f` |
| Border of a control: checkbox, radio, switch; a neutral placeholder fill | `--hh-border-color-strong` | `$hh-border-color-strong` | `#d7d7d7` | `#393939` |
| Keyboard focus ring | `--bs-focus-ring-color`, `--bs-focus-ring-width`, `--hh-focus-ring-offset` | `$focus-ring-color`, `$focus-ring-width`, `$hh-focus-ring-offset` | accent as text (`--hh-accent-fg`), `2px`, `2px` | same |

The focus ring is `outline: var(--bs-focus-ring-width) solid var(--bs-focus-ring-color)` with
`outline-offset: var(--hh-focus-ring-offset)` (mixin `hh-focus-ring`, see `docs/ui/colours.md`), not
Bootstrap's box-shadow (`$focus-ring-box-shadow` is `none`). High contrast redefines the two tokens
(3px, `--bs-emphasis-color`), a surface in another colour redefines the colour. A field's own focus
on a click (accent border plus a 50 % accent glow) stays the component variable
`$input-focus-box-shadow`.

## Deprecated

Deprecated since 1.20. The core uses none of them.

### Sass variables

| Former | New |
|---|---|
| `$text-color-main` | `$body-color` |
| `$text-color-highlight` | `$body-emphasis-color` |
| `$text-color-secondary` | `$body-secondary-color` |
| `$text-color-soft2` | `$body-tertiary-color` |
| `$background-color-main` | `$body-bg` |
| `$background-color-secondary` | `$body-tertiary-bg` |
| `$background-color-page` | `$hh-page-bg` |
| `$background-color-highlight-soft` | `$hh-accent-bg-soft` |
| `$background3` | `$hh-border-color-strong` |
| `$text-color-{color}-contrast` | `$hh-{color}-contrast` |

Each with its `-dark` variable. For the transition:

- A theme that sets a former name sets the new one: `variables-deprecated.scss`, imported after the
  theme's variables and before the core's, declares the former names (`null` unless the theme sets
  them) and sets the new names from them. The end of `variables.scss` sets the former names from
  the new ones, for a theme's `build.scss` that still reads them.
- PHP: `ThemeHelper::DEPRECATED_VARIABLES` maps the former names to the new ones. A theme or the
  custom SCSS setting a former name sets the new one, and `Theme::variable()` with a former name
  returns the new one and logs a deprecation warning.

Removed without a successor (no theme with SCSS and no module sets or reads them):
`$text-color-default`, `$text-color-soft`, `$text-color-soft3`, `$background4`,
`$background-color-highlight`, the alert variables `${background,text,border}-color-{success,warning,danger}`
and, deprecated since 1.18, `$default`, `$link`, `$text-color-contrast` (`Theme::variable()` still
answers it with `#fff`). A theme that sets one of them no longer
changes anything.

### Tokens

Every deprecated token is an alias of its replacement (`var()`), so it follows the theme and the
colour mode. A module reading one looks the same where the values match, and takes the core's
colour where they differ: `--hh-text-color-default` `#4b4b4b` → `#555`, `--hh-text-color-soft3`
`#bac2c7` and `--hh-background4` `#b2b2b2` → `#aeaeae`, the alert colours → Bootstrap's. They are
declared in one block of `_root.scss`, again in each colour mode, because a custom property
computes its `var()` where it is declared.

| Deprecated | Replacement |
|---|---|
| `--hh-text-color-main`, `--hh-text-color-default`, `--hh-text-color-soft` | `--bs-body-color` |
| `--hh-text-color-highlight` | `--bs-emphasis-color` |
| `--hh-text-color-secondary` | `--bs-secondary-color` |
| `--hh-text-color-soft2`, `--hh-text-color-soft3` | `--bs-tertiary-color` |
| `--hh-background-color-main` | `--bs-body-bg` |
| `--hh-background-color-page` | `--hh-page-bg` |
| `--hh-background-color-secondary` | `--bs-tertiary-bg` |
| `--hh-background3` | `--hh-border-color-strong` |
| `--hh-background4` | `--bs-tertiary-color` |
| `--hh-background-color-highlight` | `--bs-accent-bg-subtle` |
| `--hh-background-color-highlight-soft` | `--hh-accent-bg-soft` |
| `--hh-{background,text,border}-color-{success,warning,danger}` | `--bs-{color}-bg-subtle`, `--bs-{color}-text-emphasis`, `--bs-{color}-border-subtle` |
| `--hh-text-color-contrast` (always white) | `--bs-white`; on a theme colour its contrast color `--hh-{color}-contrast` |

The tokens deprecated since 1.18 (`--text-color-*`, `--background-*`, `--primary`, `--default`, …)
stay aliases.

An alias works for reading. A theme that **writes** a deprecated CSS variable (Clean, Mentalify)
changes the modules that read it, not the core: it sets the Sass variable instead.

## Rules

- A core partial writes no hex colour and no `darken()` or `lighten()`. A colour is a token, or a
  `color-mix()` of tokens. Stylelint enforces it (`color-no-hex`, `function-disallowed-list`)
  outside `variables.scss`, `variables-deprecated.scss` and `_root.scss`.
- A colour that reads the same in every mode may stay literal, without a hex value: white on a
  brand fill (`var(--bs-white)`), a shadow (`rgba(0, 0, 0, .1)`), a mask (`black`). A shade of a
  brand colour is a `color-mix()` with white (the topbar and login backgrounds).
- A component token is named `--hh-{component}-{property}`; a module's `--hh-{module-id}-*`; a
  theme's `--hh-{theme prefix}-*` (`--hh-et-*`).

## Migration

1. The core uses only the variables and tokens of this concept. The light screenshots of the
   visual suite stay identical, except for the alerts, the muted and faint text of Bootstrap's
   components and Bootstrap's former bluish greys.
2. [Colours](../ui/colours.md) lists the tokens and the replacements; `module-migrate-1.20.md`
   the changes.
3. The HumHub themes (Enterprise, Clean, Mentalify) set the new Sass variable names, and no longer
   write CSS variables of the core.
4. The modules move to the new tokens and Sass variable names: hex colours, `darken()` and
   `lighten()` become tokens. custom-pages, calendar, tour-maker, meeting and tasks have the most.
