# Typography

HumHub uses Bootstrap 5's type system. One value differs from Bootstrap: `$font-size-base` is
`.875rem` (14px at the browser's default). Everything else is what Bootstrap derives from it.

## Sizes follow the browser

Sizes are in rem, and `1rem` is the font size people set in their browser (16px by default).
HumHub sets no root font size. With a default font of 32px, text, controls and the spacing around
them are twice as large. Never write a `font-size` or `line-height` in px; Stylelint rejects it.

## Four roles

| Role | Variable | At the default | Use it for |
|---|---|---|---|
| Text | `$font-size-base` | 14px | Body text, fields, buttons, menus, tables. It is inherited: don't set it. |
| Secondary text | `$font-size-sm` | 12.25px | Meta lines, timestamps, hints below fields, badges, counters. Nothing is smaller. |
| Title | `$h5-font-size` | 17.5px | The title of a page, dialog, card, panel, widget or menu. |
| Content title | `$h4-font-size` | 21px | The title of a piece of content: a post with a title, a wiki page; the name on a space's or profile's header. |

In markup the same roles are `.small`, `.fs-5` and `.fs-4`.

A section inside a card (a group of fields, an accordion's row) is text size in semibold: a
weight, not a fifth size.

## Weights

`$font-weight-normal` (400) for text, `$font-weight-semibold` (600) for titles, names, labels and
emphasis. `$font-weight-medium` and `$font-weight-bold` exist, as in Bootstrap; use the variables,
never a number.

## Headings: the tag says the outline, the component says the look

- `h1`–`h6` have Bootstrap's sizes. No rule changes the size of a heading tag by where it stands.
  Bootstrap's `.h1`–`.h6` classes extend the tags, so a rule on a tag would restyle the class too.
- Pick the tag from the page's outline (`h1` the page title, `h2` its regions, `h3` their
  sections), never for its size.
- A component sets the size of its title: `.card-title`, `.modal-title`, the page header's title
  and a legacy `.panel-heading` are titles on any tag. `<h1 class="card-title">` and
  `<h2 class="card-title">` look the same.
- Outside such a component, give the heading `.fs-5` (title), `.fs-4` (content title) or `.fs-6`
  (text size).
- Bootstrap's own way works too: a utility in the markup wins over the component's size, so
  `<h1 class="modal-title fs-4">` behaves as in the Bootstrap docs.

## The exception: rendered rich text

Headings inside rendered rich text and Markdown have a scale of their own, in `em` of the
content's text. It is content written by users, and Bootstrap's `h1` is a page title, too large
inside a post. The scale lives in one place, `_richtext.scss` (h1 1.7em … h6 .85em; inside a
stream entry h1 1.45em and h2 1.3em).

## In a module or theme

- A theme changes the text size with `$font-size-base`. Don't set `$font-size-root`: it fixes the
  root size in px and switches off the browser's setting.
- Use Bootstrap's variables and utilities (`$font-size-sm`, `.small`, `.fs-5`, `.fw-semibold`).
- Icons beside text are sized in `em`, so that they grow with it.
