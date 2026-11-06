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

| Job | Token | Utility | Light | Dark |
|---|---|---|---|---|
| Body text | `--bs-body-color` | `.text-body` | `#555` | `#ddd` |
| Strong text: headings, a hovered link, an active item | `--bs-emphasis-color` | `.text-body-emphasis` | `#000` | `#fff` |
| Muted text: meta data, help text, captions, secondary icons | `--bs-secondary-color` | `.text-body-secondary` | `#7a7a7a` | `#bbb` |
| Faint text: a disabled item, a hint | `--bs-tertiary-color` | `.text-body-tertiary` | `#aeaeae` | `#999` |
| A field's placeholder | `--bs-secondary-color` | | = muted | = muted |
| Text on a background in a theme colour: a primary button, an accent badge, the topbar | `--hh-{color}-contrast` | `.text-bg-{color}` | `$color-contrast-dark` or `$color-contrast-light`, whichever reads better on the colour (black or white by default) | same |
| Status text | `--bs-{color}-text-emphasis` | `.text-{color}-emphasis` | | |

`{color}` is one of the theme colours: `primary`, `accent`, `secondary`, `info`, `success`,
`warning`, `danger`, `light`, `dark`.

## Backgrounds

| Job | Token | Utility | Light | Dark |
|---|---|---|---|---|
| The page, behind the surfaces | `--hh-page-bg` | | `#ededed` | `#000` |
| A surface: panel, card, dropdown, dialog, popover, field, table | `--bs-body-bg` | `.bg-body` | `#fff` | `#222` |
| A sunken area in a surface: comments, tabs, a filter | `--bs-tertiary-bg` | `.bg-body-tertiary` | `#f9f9f9` | `#333` |
| The fill of a hovered or active item, a neutral badge; a light separator | `--bs-secondary-bg` | `.bg-body-secondary` | `#ededed` | `#393939` |
| A selected or new item, the current day | `--bs-accent-bg-subtle` | `.bg-accent-subtle` | `#d3ecf0` | |
| A lighter selection: a checked choice, a hovered new item | `--hh-accent-bg-soft` | | `#f2f9fb` | `#223538` |
| Marked text: `<mark>`, the matched part of a suggestion | `--bs-highlight-bg` | `.mark` | `#fff4d3` | |
| Inline code | `--hh-code-bg` | | `#dbf5f8` | |
| Status: alert, notice | `--bs-{color}-bg-subtle` | `.bg-{color}-subtle` | | |

`--bs-body-bg` is the surface, not the page: Bootstrap's components take it as their background.

## Lines and focus

| Job | Token | Utility | Light | Dark |
|---|---|---|---|---|
| A separator, the border of a box | `--bs-border-color` | `.border` | `#ddd` | `#3f3f3f` |
| The border of a control (checkbox, radio, switch), a neutral placeholder fill | `--hh-border-color-strong` | | `#d7d7d7` | `#393939` |
| A status border | `--bs-{color}-border-subtle` | `.border-{color}-subtle` | | |
| A focus ring | `--bs-focus-ring-color`, `--bs-focus-ring-width` | `.focus-ring` | primary at 25 %, `0.25rem` | same |

A focus ring is `box-shadow: 0 0 0 var(--bs-focus-ring-width) var(--bs-focus-ring-color)`.

The values are those of the core theme. A theme sets them by the Sass variable of the token's
name: `$body-bg` (and `$body-bg-dark`) for `--bs-body-bg`, `$hh-page-bg` for `--hh-page-bg`,
`$mark-bg` for `--bs-highlight-bg`, `$accent-bg-subtle` for `--bs-accent-bg-subtle`. A `$hh-*`
variable is HumHub's, any other one Bootstrap's. The theme colours (`$primary`, `$accent`, …)
can also be set in the *Appearance* settings.

## Rules

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
| `--hh-text-color-soft2`, `--hh-text-color-soft3` | `--bs-tertiary-color` |
| `--hh-background-color-main` | `--bs-body-bg` |
| `--hh-background-color-page` | `--hh-page-bg` for the page, `--bs-secondary-bg` for a fill or a light separator |
| `--hh-background-color-secondary` | `--bs-tertiary-bg` |
| `--hh-background-color-highlight` | `--bs-accent-bg-subtle` |
| `--hh-background-color-highlight-soft` | `--hh-accent-bg-soft` |
| `--hh-background3` | `--hh-border-color-strong` |
| `--hh-background4` | `--bs-tertiary-color` for an icon or a line |
| `--hh-{background,text,border}-color-{success,warning,danger}` | `--bs-{color}-bg-subtle`, `--bs-{color}-text-emphasis`, `--bs-{color}-border-subtle` |
| `--hh-text-color-contrast` | `--hh-primary-contrast` |

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
| `$background3` | `$hh-border-color-strong` |
| `$text-color-{color}-contrast` | `$hh-{color}-contrast` |

Each with its `-dark` variable. The other former colour variables (`$text-color-soft`,
`$background4`, the alert colours, …) are removed.
