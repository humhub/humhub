# Colours

> Core: `protected/humhub/resources/scss/variables.scss`, `_root.scss`
> · Technology: **Both**. The tokens are CSS variables, for SCSS, Vue and inline styles alike.

Which colour to use for what. Every colour of a component is one of these tokens: a CSS variable
named after its job. A theme sets the colours, the dark mode (`data-bs-theme="dark"`) redefines
them, and a component that uses the tokens follows both without a change.

Most tokens are Bootstrap's (`--bs-*`), in the meaning Bootstrap gives them. HumHub adds a token
(`--hh-*`) only for a job Bootstrap has no token for. The concept behind it:
[Design tokens](../develop/concept-design-tokens.md).

## Text

Text that has to be read has one of two colours: the body colour, or the strong colour for headings,
links and the active item. Meta data, help text, captions and hints are body text too; a smaller
size (`.small`, `$font-size-sm`) or a lighter weight sets them back, never a lighter grey. Only the
placeholder and a disabled item are lighter.

| Job | Token | Utility | Light | Dark |
|---|---|---|---|---|
| Body text, also meta data, help text, captions, hints, secondary icons | `--bs-body-color` | `.text-body` | `#555` | `#ddd` |
| Strong text: headings, a link, a hovered or active item | `--bs-emphasis-color` | `.text-body-emphasis` | `#000` | `#fff` |
| Muted text: Bootstrap's role, the body colour (a theme may set a lighter grey, which is made readable) | `--bs-secondary-color` | `.text-body-secondary` | `#555` | `#ddd` |
| A field's placeholder: lighter than a value, 4.5:1 on the field | `--hh-input-placeholder-color` | | `#757575` | `#898989` |
| A disabled item's text and icon; never text that has to be read | `--bs-tertiary-color` | `.text-body-tertiary` | `#aaa` | `#808080` |
| Inline code | `--bs-code-color` | | `#197a88` | `#499ba7` |
| Text on a background in a theme colour: a primary button, an accent badge, the topbar | `--hh-{color}-contrast` | `.text-bg-{color}` | `$color-contrast-light` (white) where it reaches `$min-contrast-ratio` (4.5:1) on the colour, otherwise `$color-contrast-dark` (black) | same |
| A theme colour as text on a surface: a link, an icon, a label, a status message | `--hh-{color}-fg` | `.text-{color}`, `.link-{color}` | the colour, darker where it is below 4.5:1 on `--bs-secondary-bg` or on its own subtle background | the colour, lighter where it is below 4.5:1 on `--bs-secondary-bg` or on its own subtle background |
| Status text on its subtle background (an alert) | `--bs-{color}-text-emphasis` | `.text-{color}-emphasis` | Bootstrap's, darker where it is below 4.5:1 on the background | Bootstrap's, lighter where needed |

`{color}` is one of the theme colours: `primary`, `accent`, `secondary`, `info`, `success`,
`warning`, `danger`, `light`, `dark`.

A link is black next to the grey body text, below 3:1. In running text it is underlined, so its
colour is not the only thing that sets it apart (WCAG 1.4.1): in rendered rich text, a paragraph,
the hint of a field and an alert (`_link.scss`). Links that are controls (a button, a menu item, a
tab, a chip: `$hh-link-underline-exclude`) stay without the line; high contrast underlines every
other link too.

Bootstrap's `light` theme colour, the neutral button (`Button::light()`), is a step of the surface
too (`$light`), so it follows a surface chosen in the settings; `dark` stays the theme's.

A theme colour is never used as text by itself (`color: var(--bs-accent)`): an accent chosen in the
*Appearance* settings may be too light for a white card or too dark for the dark mode. Its
`--hh-{color}-fg` is the colour itself wherever it reads, and otherwise the nearest shade that
does, worked out by the Sass function `hh-readable($color, $background, $min-ratio: 4.5)`
(`_functions.scss`) into the variables `$hh-{color}-fg` and `$hh-{color}-fg-dark`. A theme sets
one of them, or raises the contrast of all by `$hh-fg-contrast-ratio`. `--hh-{color}-fg` is there
for `primary`, `accent`, `secondary`, `info`, `success`, `warning` and `danger`. A mail, which has
no tokens, takes `MailStyleHelper::getLinkColor()` for a link in the primary colour.

The text on a theme colour as a fill (a button, a badge, the top bar) is white where white reaches
`$min-contrast-ratio` on the colour, otherwise black; one of the two always does. The ratio is 4.5:1,
WCAG AA for normal text and Bootstrap's default, and the default colours are just dark enough for
white. Before 1.20 it was 3:1, which left white text at 3.1 to 3.7:1 on accent, info, success and
danger. A theme or the Custom SCSS can set it back (`$min-contrast-ratio: 3`), below AA. A mail
takes the same ratio through `ScssHelper::getColorContrast()`.

### A theme colour as text: class or token

| Where | Use | Example |
|---|---|---|
| In the markup (a view, a widget) | the Bootstrap utility | `<span class="text-danger">`, `<a class="link-accent">` |
| In SCSS | the token | `.my-label { color: var(--hh-accent-fg); }` |
| Never for text | the colour itself | `color: var(--bs-accent)` is for fills and borders only |

Both are the same colour: `.text-{color}` is generated by Bootstrap's utility API from
`$utilities-text-colors`, which the core points at the tokens (`maps.scss`), and `.link-{color}` is
overridden in `_link.scss`. `fg` (foreground) is the name Bootstrap 6 gives this role, next to
`bg`, `bg-subtle` and `contrast`, so the token can become an alias of Bootstrap's own once the core
moves to it.

In high contrast (the user's *Accessibility* option or `prefers-contrast: more`) the body text and
`--hh-{color}-fg` reach `$hh-high-contrast-ratio` (7:1, WCAG AAA) on the surfaces, and
`--bs-{color}` as a fill is darkened (or lightened) until its `--hh-{color}-contrast` text reaches
it too; the buttons follow. Panels, cards and the page header get the same 1px edge in
`--bs-border-color` (`$hh-surface-box-shadow-contrast`) instead of their soft shadow.

## Backgrounds

| Job | Token | Utility | Light | Dark |
|---|---|---|---|---|
| The page, behind the surfaces | `--hh-page-bg` | | `#ededed` | `#161616` |
| A surface: panel, card, dropdown, dialog, popover, field, table | `--bs-body-bg` | `.bg-body` | `#fff` | `#222` |
| A sunken area in a surface: comments, tabs, a filter | `--bs-tertiary-bg` | `.bg-body-tertiary` | `#f9f9f9` | `#333` |
| The fill of a hovered or active item, a neutral badge; a light separator | `--bs-secondary-bg` | `.bg-body-secondary` | `#ededed` | `#393939` |
| A selected or new item, the current day, a hovered row | `--bs-accent-bg-subtle` | `.bg-accent-subtle` | `#d1e6e9` | `#213538` |
| A lighter selection: a checked choice, a hovered new item; inline code | `--hh-accent-bg-soft` | | `#edf5f6` | `#212a2b` |
| Marked text: `<mark>`, the matched part of a suggestion | `--bs-highlight-bg` | `.mark` | `#fff3cd` | `#4e421d` |
| The top bar and the account menu hanging from it; their text | `--hh-topbar-bg`, `--hh-topbar-color` | | primary, its contrast colour | `#111`, `#fff` |
| Status: alert, notice | `--bs-{color}-bg-subtle` | `.bg-{color}-subtle` | | |

`--bs-body-bg` is the surface, not the page: Bootstrap's components take it as their background.
It can be set for each mode in *Administration → Appearance → Theme*. The page, the other surfaces
and the lines are mixed from it (`mix(black, $body-bg, 7%)` for the page), so they follow a
tinted surface; a theme can still set each of them. A tinted background (a selection, a status,
marked text) is its colour mixed into the surface: 20 % for a subtle background, 40 % for its
border, 8 % for the lighter selection. In the dark mode it is lighter than a card.

## Lines and focus

| Job | Token | Utility | Light | Dark |
|---|---|---|---|---|
| A separator, the border of a box, a neutral placeholder fill | `--bs-border-color` | `.border` | `#ddd` | `#3f3f3f` |
| The border of an input (field, select, checkbox, radio, switch): 3:1 on its fill (WCAG 1.4.11) | `--hh-input-border-color` (`$input-border-color`) | | `#949494` | `#6d6d6d` |
| A status border | `--bs-{color}-border-subtle` | `.border-{color}-subtle` | | |
| The keyboard focus ring | `--bs-focus-ring-color`, `--bs-focus-ring-width`, `--hh-focus-ring-offset` | | accent as text (`--hh-accent-fg`), `2px`, `2px` | same |

The focus ring is `outline: var(--bs-focus-ring-width) solid var(--bs-focus-ring-color)` with
`outline-offset: var(--hh-focus-ring-offset)`, drawn by the mixin `hh-focus-ring`
(`hh-focus-ring(true)` draws it inside the edge, for a box that clips or a row that fills its box).
`_base.scss` draws it on every `:focus-visible`; a component only adds it where Bootstrap turned it
off (`outline: 0`), or redefines `--bs-focus-ring-color` on a surface in another colour (the top
bar: `--hh-topbar-color`). High contrast makes it 3px in `--bs-emphasis-color`. A text field shows
it after Tab only; a click shows the field's glow (`$input-focus-box-shadow`): `ui.appearance` keeps
`data-hh-input-modality` (`keyboard`, `pointer`) on `<html>`.

The values are those of the core theme. A theme sets them by the Sass variable of the token's
name: `$body-bg` (and `$body-bg-dark`) for `--bs-body-bg`, `$hh-page-bg` for `--hh-page-bg`,
`$mark-bg` for `--bs-highlight-bg`, `$accent-bg-subtle` for `--bs-accent-bg-subtle`. A `$hh-*`
variable is HumHub's, any other one Bootstrap's. The dark mode's value is the `-dark` variable, e.g. a top bar in
the primary colour in both modes:

```scss
$hh-topbar-bg-dark: var(--bs-primary); // Not `$primary`: a theme's variables come before the core's
$hh-topbar-color-dark: var(--hh-primary-contrast);
``` The theme colours (`$primary`, `$accent`, …)
can also be set in the *Appearance* settings.

## Rules

- **Text that has to be read** is `--bs-body-color` (`.text-body-secondary` is the same colour) or
  `--bs-emphasis-color`. Never `--bs-tertiary-color` or `.text-body-tertiary`: they are for a
  disabled item only.
- **Use the token of the job**, not the one with the right look: a hovered item is
  `--bs-secondary-bg`, not `--hh-page-bg`, although both are `#ededed` in the light mode.
- **No literal colour.** No hex value, no `darken()` or `lighten()` of a Sass variable: they are
  fixed at compile time and don't change with the colour mode. A shade is a `color-mix()` of
  tokens, e.g. `color-mix(in srgb, var(--bs-emphasis-color) 5%, var(--bs-tertiary-bg))`.
- A colour that reads the same in every mode may stay literal: white on a brand fill
  (`var(--bs-white)`), a shadow.
- A component's own colour is a component token that refers to these: `--hh-{component}-*`, in a
  module `--hh-{module-id}-*`.

## Replaced tokens and variables

Deprecated since 1.20. The former tokens still work, as aliases of their replacements.

| Instead of | Use |
|---|---|
| `--hh-text-color-main`, `--hh-text-color-default`, `--hh-text-color-soft` | `--bs-body-color` |
| `--hh-text-color-highlight` | `--bs-emphasis-color` |
| `--hh-text-color-secondary` | `--bs-secondary-color` |
| `--hh-text-color-soft2`, `--hh-text-color-soft3` | `--bs-secondary-color` for text, `--bs-tertiary-color` for a disabled item |
| `--hh-background-color-main` | `--bs-body-bg` |
| `--hh-background-color-page` | `--hh-page-bg` for the page, `--bs-secondary-bg` for a fill or a light separator |
| `--hh-background-color-secondary` | `--bs-tertiary-bg` |
| `--hh-background-color-highlight` | `--bs-accent-bg-subtle` |
| `--hh-background-color-highlight-soft` | `--hh-accent-bg-soft` |
| `--hh-background3` | `--bs-border-color` |
| `--hh-background4` | `--bs-border-color` for a line, `--bs-secondary-color` for an icon |
| `--hh-{background,text,border}-color-{success,warning,danger}` | `--bs-{color}-bg-subtle`, `--bs-{color}-text-emphasis`, `--bs-{color}-border-subtle` |
| `--hh-text-color-contrast` | The contrast color of the background (`--hh-{color}-contrast`), or `--bs-white` on a fixed color |

| Instead of the Sass variable | Use |
|---|---|
| `$text-color-main` | `$body-color` |
| `$text-color-highlight` | `$body-emphasis-color` |
| `$text-color-secondary` | `$body-secondary-color` |
| `$text-color-soft2` | `$body-tertiary-color` |
| `$background-color-main` | `$body-bg` |
| `$background-color-secondary` | `$body-tertiary-bg` |
| `$background-color-page` | `$hh-page-bg` |
| `$background-color-highlight-soft` | `$hh-accent-bg-soft` |
| `$background3` | `$border-color` |
| `$text-color-{color}-contrast` | `$hh-{color}-contrast` |

Each with its `-dark` variable. The other former colour variables (`$text-color-soft`,
`$background4`, the alert colours, …) are removed.
