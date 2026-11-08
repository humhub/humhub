# Keyboard

> Core: the focus ring (`hh-focus-ring` mixin, `humhub.ui.appearance` for the input modality), the
> skip link (`View::renderBodyBeginHtml()`), the focus after a page swap (`humhub.client.pjax`),
> `humhub.ui.modal` and `humhub.ui.focus` (focus hand-over), `humhub.ui.tooltip`, `humhub.action`
> · Technology: **Both**. The rules hold for PHP widgets and Vue components alike.

How HumHub works with a keyboard alone. These rules apply to every component. A component page
states only what it adds (which keys move inside it) and never contradicts them. The yardstick is
WCAG 2.1 AA: everything operable (2.1.1), no keyboard trap (2.1.2), blocks can be skipped (2.4.1),
the focus order follows the page (2.4.3), the focus is visible (2.4.7), what hover shows is also
there on focus and can be dismissed (1.4.13), controls have a name and a role (4.1.2), and status
messages are announced (4.1.3).

## Reaching everything

- **Everything the mouse does, the keyboard does.** Tab and Shift+Tab reach every control, Enter
  and Space activate it, the arrow keys move inside a composite control (a menu, a list, tabs, a
  toolbar), and Escape closes what was opened.
- **Native elements first.** An action is a `<button>`, a navigation is an `<a href>`. A
  `role="button"` on another element is the exception; it then has `tabindex="0"` and reacts to
  Enter and Space. The action framework provides that for every `data-action-click` element that
  is neither a link nor a button, so a forgotten handler does not lock keyboard users out.
- **What hover shows, focus shows.** A control that appears when the pointer rests on its
  container (the ⋮ menu of a comment, the actions of a tile) also appears while the focus is
  inside the container (`:focus-within`), and stays while its menu is open
  (`:has(.dropdown-menu.show)`). The container holds a focusable element before the control (the
  name link), so Tab enters the container before it reaches the control. A control hidden with
  `display: none` or `visibility: hidden` is out of the tab order, which is fine only with these
  two selectors in place. On touch devices (`hover: none`) the control is always visible.
- **The order is the visual order.** No positive `tabindex`. After a full page load the first stop
  is the skip link to the main content, then the top bar, then the content. After a pjax swap the
  focus is on the loaded content, so Tab continues there instead of at the top of the page.

## Landmarks and names

- The page frame uses the HTML landmarks: the first top bar (`#topbar-first`) is a `<header>`, the
  second (`#topbar-second`, the main navigation) a `<nav>` with an `aria-label`, the content
  `<main id="main-content">`, the widget column an `<aside>`. Every `<nav>` has a name, so a screen
  reader can tell them apart. In the Enterprise theme the sidebar is the `<nav>`.
- One `<h1>` per page, rendered by the [Page Header](page-header.md). A widget's title is a
  heading below it, never an `<h1>`.
- The current page in a navigation carries `aria-current="page"`.
- An icon-only control has a name: the tooltip text becomes its `aria-label`
  ([Icon-only buttons](../develop/wcag/button-icon-aria-label.md)). A link that shows only an icon
  or an image has a name too (`aria-label`, or the image's `alt`). A count on a control is part of
  its name ("Notifications, 3 unread").

## Moving the focus

The focus never lands on `<body>` after something the user did. Whenever the element that holds
the focus goes away, the component that removed it hands the focus on:

- **A dialog** moves the focus to its first field on open, else to the dialog itself. It keeps the
  focus inside while it is open and returns it to its trigger on close. A trigger inside a menu
  that has closed meanwhile hands over to the menu's toggle. If the trigger is gone by then (the
  row was deleted, the list was reloaded), the focus goes to the content container, the same
  target pjax uses.
- **A removed entry** (a deleted post, comment or row) passes the focus to the next entry, else to
  the previous one, else to the head of the list (its filter or its heading).
- **Loaded content** ("Show more", the next page of comments) gets the focus on the first new
  entry, or on the loaded continuation link.
- **An edited entry** keeps the focus: after saving in place, the focus is on the entry, not at
  the top of the stream.
- **A pjax swap** focuses the new content, unless the new content sets the focus itself.

## Popups and Escape

- **Escape closes the innermost open thing**: a tooltip, then a dropdown or picker list, then the
  dialog. A dialog does not close while a popup inside it is open: the tooltip's Escape handler
  stops the event once it hid a tooltip, so the dialog behind it stays. What closes returns the
  focus to its trigger.
- **Dropdowns** are Bootstrap's: Enter, Space or ArrowDown on the toggle opens, the arrow keys
  move between the items, Escape closes and refocuses the toggle. Items are links with an `href`
  or buttons, never a bare `<a>` or `<li>`, otherwise Tab and the arrows skip them. The toggle
  has `aria-expanded`. See [Context Menu](context-menu.md).
- **Tooltips** appear on focus as they do on hover, but only when the focus came from the
  keyboard (`data-hh-input-modality="keyboard"`, kept by `humhub.ui.appearance`), not after a
  click. They vanish on blur and on Escape, without moving the focus. See [Tooltip](tooltip.md).
- **The hover card** is mouse-only. It is an enhancement, and everything in it is also behind the
  profile link, which the keyboard reaches. See [Hover Card](hover-card.md).

## Announcing

- What an action changes somewhere the focus is not, the status bar announces (`humhub.ui.status`,
  `aria-live="polite"`, errors assertive). See [Toast](toast.md).
- Loading, empty and error states carry `role="status"` or `role="alert"`. See
  [Loading, Empty & Error States](loading-empty-error.md).
- A counter that updates live (unread notifications) updates its control's name. It does not
  interrupt.

## The editor

- Tab leaves the richtext editor to the next control (the submit button), Shift+Tab to the
  previous one.
- In a list, Tab indents the item and Shift+Tab outdents it. Enter on an empty item leaves the
  list, then Tab leaves the editor.
- In a table, Tab moves to the next cell and Shift+Tab to the previous one. After the last cell,
  Tab places the cursor after the table; before the first cell, Shift+Tab places it before. A
  row is added from the table menu, not by Tab.
- The toolbar is a `role="toolbar"`: the arrow keys move between its buttons, Enter or ArrowDown
  opens a dropdown, Escape closes it. The mention and emoji suggestions take ↑/↓ and Enter, and
  Escape closes them.

## Checking

The manual walk, on the dashboard, a space stream and a dialog, in the Community and the
Enterprise theme:

1. Reload and press Tab: the skip link appears. Enter lands in the main content.
2. Tab through the page: every control the mouse can reach is reached, in the visual order, and
   shows the focus ring. Nothing appears only on hover.
3. Open every popup from the keyboard (menu, picker, tooltip, dialog): Escape closes it, and the
   focus is back on the trigger.
4. Delete an entry, load more, save an edit: the focus is where [Moving the focus](#moving-the-focus)
   says.
5. In the editor: Tab leaves it, and a list and a table can be left.

Automated: the internal **Accessibility Audit** module (`humhub/a11yAudit`, axe-core) runs on the
develop instance. The rules that matter for the keyboard are `button-name`, `link-name`,
`landmark-one-main`, `landmark-no-duplicate-banner`, `region`, `tabindex`,
`scrollable-region-focusable`, `aria-allowed-attr` and `aria-required-children`; the three views
stay free of them. The acceptance test `KeyboardCest` (content module) covers four scenarios: the
skip link, the stream entry menu, the focus after a dialog closes, and the comment menu by Tab.
Vitest covers the JS: tooltips on focus and Escape, the modal's focus return, Enter and Space in
the action framework, Tab in editor tables.

## Implementation in core

**Today:** the focus ring for every control, with the input modality (#8719); the skip link; the
focus after a pjax swap (#8416) and after "Show more" in the stream (#8408); the Vue dialog
returns the focus (`UiModal.vue`); the pickers, the space chooser and the item browser follow the
rules; the editor toolbar has keyboard tests; icon-only buttons derive their `aria-label` from the
tooltip.

**Gaps:**

| Gap | Change |
|---|---|
| The comment ⋮ menu is `visibility: hidden` until `.single-comment:hover`, and so out of the tab order | `:focus-within:not(:has(.single-comment:focus-within))` next to the `:hover` rule in `_comment.scss`, the same guard as the hover rule so a focused reply does not show its parent's menu; always visible on `hover: none` |
| Tooltips are initialised on `mouseover` only, so the keyboard never sees one | A `humhub.ui.tooltip` module replaces the block in `humhub.ui.additions`: hover, `focusin` under keyboard modality, hidden on `focusout` and Escape |
| `humhub.ui.modal` leaves the focus on `<body>` after a dialog closes | A `humhub.ui.focus` module (`restore()`, `handOver()`, `focusContent()`); the modal remembers the trigger on `show.bs.modal` and restores it on `hidden.bs.modal`, else the dropdown toggle, else the content |
| The top bars are `<div>`s: no `<nav>`, no `<header>`, no `aria-current` | Landmark tags in `views/layouts/main.php` (ids and classes unchanged), `aria-current="page"` in `TopMenu` and `LeftNavigation`; the Enterprise theme layout follows |
| A `data-action-click` on a non-native element reacts to clicks only | `humhub.action` binds Enter and Space for `[data-action-click]` elements that are neither links nor buttons |
| A deleted stream entry or comment drops the focus to `<body>` | `StreamEntry.delete()`/`remove()` through `humhub.ui.focus.handOver()`, the comment island through its own `focusHandOver()`: the next entry, else the previous one, else the list |
| An entry replaced after an edit or a reload drops the focus | `StreamEntry.replace()` focuses the new node when the old one held the focus |
| Tab in the last table cell adds a row, so a table cannot be left by Tab | Tab after the last cell leaves the table, Shift+Tab before the first (`tab_behavior/keymap.js`) |

**Modules:** the same rules, checked with the module search for four patterns: a control hidden
until `:hover` without `:focus-within`, a dropdown toggle or icon link without a name, an `<h1>`
inside a widget, and Bootstrap 3's `data-toggle`. The modules found and their fixes are listed in
the pull requests, not here.

## Decisions

- **No global shortcuts.** Keyboard operability is Tab, Enter, Space, the arrows and Escape.
  Shortcuts (`/` for the search) are a comfort feature with duties of their own (a visible list, a
  switch, no conflicts with screen readers and browsers) and not part of this page.
- **The hover card stays mouse-only.** The profile link is the keyboard's way to the same content.
- **The date picker** is not changed here. [Date Picker](date-picker.md) replaces jQuery UI's
  with its own keyboard rules.
