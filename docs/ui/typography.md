# Typography

The interface has six text styles. Each fixes size, weight, line height and colour together, and
each has a clear use. A component takes one of them; nothing in the interface picks a size or a
weight of its own. Rendered content (rich text, Markdown) has a scale of its own, and a few
components have a special case, documented with the component.

The styles sit on Bootstrap 5's type system: `$font-size-base` is `.875rem` (14px), and the
heading scale is shifted so that Bootstrap's habitual classes give these styles (see
[Bootstrap's names](#bootstraps-names)).

## Sizes follow the browser

Sizes are in rem, and `1rem` is the font size people set in their browser (16px by default).
HumHub sets no root font size. With a default font of 32px, text, controls and the spacing around
them are twice as large. Never write a `font-size` or `line-height` in px; Stylelint rejects it.

## Text styles

| Style | Size | Weight | Line height | Colour | Use it for |
|---|---|---|---|---|---|
| **Text** | 14px | 400 | 22px | `--bs-body-color` | Running text, descriptions, table cells, list rows |
| **Strong text** | 14px | 600 | 22px | `--bs-emphasis-color` | A name (person, space, content), emphasis in a sentence, a section inside a card or dialog |
| **Control** | 14px | 500 | 22px | the control's | The label of a button, tab, menu entry or form field |
| **Meta** | 12px | 400 | 18px | `--bs-body-color` | Time, author line, hint below a field, label of a count, any secondary detail |
| **Title** | 16px | 600 | 24px | `--bs-emphasis-color` | The title of a page, card, panel, dialog, widget or menu |
| **Large title** | 18px | 600 | 28px | `--bs-emphasis-color` | The title of a piece of content: a post with a title, a wiki page, an entity card |

Rules:

- Size and line height are the design system's pairs (12/18, 14/22, 16/24, 18/28 px at the browser's default font
  size). Line heights are ratios (`$line-height-base` is `divide(22, 14)`), so they grow with the text, and text of
  another size inside has a line of its own.
- Three weights, each with its job: 400 for text and meta, 500 for controls, 600 for names and
  titles.
- Nothing is smaller than Meta.
- Meta differs from Text by its size only; both are in the body colour (see
  [Colours](colours.md#text)).

### Where each style comes from

| Style | In a component (core SCSS) | In markup |
|---|---|---|
| Text | inherited | nothing to write |
| Strong text | `@include hh-text-style(strong)` | `<strong>`, `.fw-semibold`: the weight, in the colour around it; standing alone (a name in a list) also `.text-body-emphasis` |
| Control | `@include hh-text-style(control, $color: false)`; Bootstrap's buttons, tabs, menus and labels have it already | (the component) |
| Meta | `@include hh-text-style(meta)` | `.small` |
| Title | `@include hh-text-style(title)`; `.card-title`, `.modal-title`, `.panel-heading`, the page header's and the section menu's title have it | `h5`, or `.fs-5` on another heading tag |
| Large title | `@include hh-text-style(title-lg)` | `h4`, or `.fs-4` on another heading tag |

The styles are one Sass map, `$hh-text-styles` (`_mixins.scss`); `hh-text-style()` writes a style's
properties, `$color: false` leaves the colour to the element (a button, a link).

A heading tag has the weight and colour of a title (`$headings-font-weight`, `$headings-color`), and
`.fs-5`/`.fs-4` set only the size; `h4` and `.fs-4` also have the Large title's line height, `.small` Meta's
(`_base.scss`). So `.fs-5` on a `div` is not a Title; put it on the heading.

## Bootstrap's names

Bootstrap names its sizes twice: the heading scale `h1`–`h6` (`.fs-1`–`.fs-6`) and the three
component sizes `$font-size-sm`, `-base`, `-lg`. HumHub shifts the bottom of the heading scale by
one step (`$h5-font-size: 1rem`, `$h4-font-size` = `$font-size-lg`), so that a habitual class
gives a text style:

| Bootstrap | Size | Text style |
|---|---|---|
| `h1`, `.fs-1` | 35px | (login page only) |
| `h2`, `.fs-2` | 28px | (error page only) |
| `h3`, `.fs-3` | 24.5px | (the header's name, a special case) |
| `h4`, `.fs-4`, `$font-size-lg` | 18px | Large title |
| `h5`, `.fs-5` | 16px | Title |
| `h6`, `.fs-6`, `$font-size-base` | 14px | Text |
| `.small`, `$font-size-sm` | 12px | Meta |

Bootstrap 6 replaces both names with one scale (`xs` … `6xl`); the text styles stay the
interface's vocabulary across that change.

## Headings: the tag says the outline, the style says the look

- Pick the tag from the page's outline (`h1` the page title, `h2` its regions, `h3` their
  sections), never for its size.
- A component sets the style of its title: `<h1 class="card-title">` and `<h2 class="card-title">`
  look the same.
- Outside such a component, give the heading the style's class: `.fs-5` (Title) or `.fs-4`
  (Large title). A utility in the markup wins over the component's style.
- No rule changes the size of a heading tag by where it stands. Bootstrap's `.h1`–`.h6` classes
  extend the tags, so a rule on a tag would restyle the class too.

## Special cases of components

These belong to one component and are documented with it, not used elsewhere:

| Component | What | Value |
|---|---|---|
| [Space & profile header](container-header.md) | The name on the cover image | 24.5px, 600 (`$container-header-title-font-size`) |
| [Space & profile header](container-header.md) | The numbers of the counters | 21px, 600 (`$container-header-count-font-size`) |
| [Top bar](top-bar.md) | The network's name | 21px (`$topbar-brand-font-size`) |
| [Top bar](top-bar.md) | The labels of the main navigation | 12px, 600, capitals |
| Table ([Data table](data-table.md), grid view, `.table`) | A column's header (`th`) | Meta, but 600 (`$table-th-font-weight`); in a data table, the sorted column's in the emphasis colour |
| [Badges & status](badges.md) | A badge | Meta's size in `em`, 600, capitals (`$badge-font-size`, `$badge-font-weight`) |

## Content: rich text and Markdown

Rendered content is written by users and has a scale of its own, in `em` of the content's text
(`_richtext.scss`, the only place that sizes `h1`–`h6` of content):

| Element | Size | Inside a stream entry |
|---|---|---|
| `h1` | 1.7em | 1.45em |
| `h2` | 1.5em | 1.3em |
| `h3` | 1.2em | |
| `h4` | 1.1em | |
| `h5` | 1em | |
| `h6` | .85em | |
| Paragraph, list, table | Text | |

Headings in content are weight 400 in the body colour (`_markdown.scss`), not the interface's
titles. Bold text (`strong`, `b`) has the weight of Strong text (600), as everywhere.

Changes to the look of content (e.g. telling `h1` and `h2` apart by weight rather than size)
are made in this table and in `_richtext.scss`, independent of the interface's styles.

## In a module or theme

- A theme changes the text size with `$font-size-base`. Don't set `$font-size-root`: it fixes the
  root size in px and switches off the browser's setting.
- A theme's SCSS is compiled with the core's, so it uses `hh-text-style()` and the Sass variables.
- Icons beside text are sized in `em`, so that they grow with it.

A module's own stylesheet is compiled on its own, without the core's variables. If it imports
Bootstrap's variables, `$font-size-sm` and `$font-size-lg` there are Bootstrap's (14px and 20px),
not these. So a module:

- Takes a style in the markup where it can: the core's components (`.card-title`,
  `.panel-title`, `.modal-title`), `.small`, `.fw-semibold`, `.fs-5`, `.fs-4`.
- In its CSS, takes a style's size from the core's CSS variables, never a px or `em` value:

  | Style | CSS variable |
  |---|---|
  | Text | `var(--bs-body-font-size)` (inherited, so mostly not written) |
  | Meta | `var(--hh-font-size-meta)` |
  | Title | `var(--hh-font-size-title)` |
  | Large title | `var(--hh-font-size-title-lg)` |

  Weights are the style's: 400, 500 or 600.
- Writes spacing in rem on Bootstrap's steps (`.25rem`, `.5rem`, `1rem`, `1.5rem`, `3rem`, see
  [Styling with Bootstrap 5](bootstrap.md#spacing)), radii as `var(--bs-border-radius-sm)`,
  `var(--bs-border-radius)`, `var(--bs-border-radius-lg)`.
