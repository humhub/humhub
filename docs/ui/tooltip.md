# Tooltip

> Found in the design: every icon button, truncated names (files, tables), counters, chips that
> filter ("Filter by Marketing")
> · Core: Bootstrap 5 tooltips via `humhub.ui.tooltip` (`.tt`, `data-bs-toggle="tooltip"`,
> `Button::tooltip()`)
> · Technology: **Both** (one shared tooltip for PHP and Vue markup)

A short text label that appears next to a control. It has two jobs:

- **Name a control** that shows only an icon ("Add user", "More actions").
- **Show text that was cut off** ("Quarterly report final version 2026.pdf").

A tooltip never holds information or actions that exist nowhere else. For a richer preview of a
person or space, use the [Hover Card](hover-card.md).

![Tooltip and hover card](images/tooltip-hover-card.svg)

## Behaviour

- **Mouse:** appears after a short pause (about 250ms). Moving to the next control shortly after
  shows its tooltip at once, so scanning a toolbar is quick.
- **Keyboard:** appears on focus, but only when focus came from the keyboard (Tab), not after a
  click. A link or button shows the tooltip of the one element inside it that has one (the date in
  a permalink). An icon whose tooltip says something (visibility, "Last updated") is a tab stop of its
  own: `Icon::focusable()`, named by its tooltip.
- **Hoverable:** the tooltip stays while the pointer is on it, so it can be read and enlarged. It hides
  150ms after the pointer left the control, time enough to move onto the tooltip (WCAG 1.4.13).
- **Touch:** no tooltips. A tap triggers the control.
- **Closes** on leaving, on a click, on Escape and on scrolling.
- **Placement:** above the control by default, otherwise where there is room, and always inside
  the window.
- **Cut-off text:** only shows when the text is actually truncated.
- **Text:** a few words, without a full stop. For icon buttons, the same text as the button's
  accessible name.

## Accessibility

- An icon button's tooltip is its accessible name (`aria-label`) and not an extra description.
  Where the tooltip differs from the name, it is linked with `aria-describedby`.
- Escape hides the tooltip without moving focus.
- Tooltips are never the only way to get information (they don't exist on touch).
- Reduced motion: no fade or slide.

## Markup and styles

Bootstrap's **`.tooltip`** and Tooltip JS. The shared behaviour (delay, quick follow-up, keyboard
focus only, no touch) is Bootstrap's options plus a small delegated binding, not a tooltip of its
own. Class names and SCSS follow [Styling with Bootstrap 5](bootstrap.md).

- **Markup:** `data-bs-toggle="tooltip"` with `data-bs-title` (or `title`), as today. Icon
  buttons get it from `Button::iconOnly()` and `IconButton.vue`.
- **Options:** `delay: { show: 0, hide: 150 }` today, `{ show: 500, hide: 150 }` planned (`$tooltip-delay` as a CSS variable read by the
  JS), `trigger: 'hover'` plus the keyboard focus from `humhub.ui.tooltip`, which shows a tooltip
  on `focusin` only while `humhub.ui.appearance` reports keyboard modality, hides it on `focusout`
  and on Escape ([Keyboard](keyboard.md#popups-and-escape)); no tooltip on touch.
- **Truncated text:** `data-tooltip="overflow"` on the element that cuts its text off with an ellipsis
  shows the full text as tooltip, only while it is cut off; a focused link or button around it shows
  it too (the name in the top bar's account menu, the site name, the space description in its
  header). Text that has room wraps instead (the name in the profile header).
- **File:** `_tooltip.scss`, behind `$prev-tooltip`.
- **Sass variables:** Bootstrap's `$tooltip-font-size` (already set), `$tooltip-max-width`,
  `$tooltip-bg`, `$tooltip-color`, `$tooltip-padding-x/y`, `$tooltip-border-radius`,
  `$tooltip-opacity`.

## Implementation in core

**Today:** Bootstrap 5 tooltips, initialised by `humhub.ui.additions` on `.tt` and
`[data-bs-toggle="tooltip"]` elements. `Button::tooltip()` sets them. Vue components often use the
native `title` attribute instead (e.g. `PageToolbar` actions).

**Gaps:**

| Gap | Proposal |
|---|---|
| Initialised on `mouseover` only, so a keyboard user never sees one | `humhub.ui.tooltip`: the same delegated binding on hover and on keyboard focus, hidden on `focusout` and Escape ([Keyboard](keyboard.md#popups-and-escape)) |
| Delay, quick follow-up, no touch | One shared tooltip, bound by delegation, that reads `data-tooltip` (or the existing Bootstrap attributes) from PHP and Vue markup alike |
| Vue uses native `title` | Switch to the shared tooltip, so icon buttons look the same everywhere |

## Open questions

1. **Keep Bootstrap tooltips** and restyle them, or replace them with one small shared
   implementation? Bootstrap has no quick follow-up; the keyboard focus comes from `humhub.ui.tooltip`.
