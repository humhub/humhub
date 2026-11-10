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
  Enter and Space. The action framework provides the Enter and Space handling for every
  `data-action-click` element that is neither a link nor a button; the `tabindex` and the `role`
  remain the markup's job.
- **What hover shows, focus shows.** A control that appears when the pointer rests on its
  container (the ⋮ menu of a comment, the actions of a tile) also appears while the focus is
  inside the container (`:focus-within`), and stays while its menu is open
  (`:has(.dropdown-menu.show)`). Either the control stays in the tab order, hidden by
  `opacity: 0`, and shows the moment it takes the focus; or it leaves the tab order
  (`display: none`, `visibility: hidden`) and then its container holds a focusable element before
  it (the name link), so Tab enters the container first. The hiding applies under
  `@media (hover: hover)` only: a touch screen always shows the control.
- **The order is the visual order.** No positive `tabindex`. After a full page load the first
  stops are the skip links (to the content, the navigation and the sidebar; a link whose target
  the page lacks is hidden), then the top bar, then the content. The content is the column beside
  the page's own menu (`#layout-content-column` in a space, a profile, the account and the administration),
  else the main area (`#main-content`), so the link skips that menu as well. After a pjax swap the
  focus is on the loaded content, so Tab continues there instead of at the top of the page.

## Landmarks and names

- The page frame uses the HTML landmarks: the first top bar (`#topbar-first`) is a `<header>`, the
  second (`#topbar-second`, the main navigation) a `<nav>` with an `aria-label`, the content
  `<main id="main-content">`, the widget column an `<aside>`. Every `<nav>` has a name, so a screen
  reader can tell them apart. In the Enterprise theme the sidebar is the `<nav>`. The pages without
  a session (sign in, sign up, password recovery) put their panel into the same
  `<main id="main-content">` and the footer menu into a `<footer>`; the panel title is their `<h1>`.
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

## Feeds

A list that grows while it is read (the stream, the comments of a post) is a **feed**, after the
WAI-ARIA feed pattern: the list is `role="feed"` with a name while it has entries, each entry is
`role="article"` with `tabindex="0"` and a name (`aria-labelledby` on its heading), and the list
carries `aria-busy="true"` from the request until the loaded entries are in the document. `humhub.ui.feed`
gives every such list the keys, so a module adds the roles and nothing else. They are the keys of
the pattern, like the arrows in a toolbar, not shortcuts: they work only while the focus is in the
feed, and a text field or editor keeps them.

- **PageDown and PageUp** move the focus to the next or previous entry and scroll it to the top.
  From a control inside an entry they count from that entry, from a control between entries
  ("Show more") from its place.
- **Ctrl+End and Ctrl+Home** leave the feed: to the first control after it (the sidebar, after
  the stream) or to the last control before it.
- **Tab** from an entry goes to its first control, as everywhere.
- **At the end,** PageDown asks the feed for more (`humhub:feed:more` on the feed element, with
  `detail.direction`) and, once `aria-busy` is gone, lands on the first new entry. A feed with
  nothing more to load leaves the focus where it is. The event bubbles, so a feed answers only
  the one dispatched on itself, not one from a feed inside it.
- **A feed inside an entry** (the comments inside a post) takes the keys while the focus is in it
  and has entries: PageDown moves between its comments, Ctrl+End leaves it into the post, from
  where PageDown reaches the next post. While it is empty, the feed around it answers.

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
- The toolbar is a `role="toolbar"` and one Tab stop: only one of its buttons is in the tab
  order, the arrow keys move between them, Enter or ArrowDown opens a dropdown, Escape closes it,
  and Tab leaves the toolbar for the text. The mention and emoji suggestions take ↑/↓ and Enter, and
  Escape closes them.

## Checking

The manual walk, on the dashboard, a space stream and a dialog, in the Community and the
Enterprise theme:

1. Reload and press Tab: the skip links appear one after the other. Enter lands in the main
   content, the navigation or the sidebar.
2. Tab through the page: every control the mouse can reach is reached, in the visual order, and
   shows the focus ring. Nothing appears only on hover.
3. Open every popup from the keyboard (menu, picker, tooltip, dialog): Escape closes it, and the
   focus is back on the trigger.
4. Delete an entry, load more, save an edit: the focus is where [Moving the focus](#moving-the-focus)
   says.
5. In the editor: Tab leaves it, and a list and a table can be left.
6. In the stream: PageDown moves from entry to entry and loads more at the end, Ctrl+End reaches
   the sidebar; inside the comments PageDown moves between the comments.

Automated: the internal **Accessibility Audit** module (`humhub/a11yAudit`, axe-core) runs on the
develop instance. The rules that matter for the keyboard are `button-name`, `link-name`,
`landmark-one-main`, `landmark-no-duplicate-banner`, `region`, `tabindex`,
`scrollable-region-focusable`, `aria-allowed-attr` and `aria-required-children`; the three views
stay free of them, with one accepted finding: `aria-required-children` allows nothing but articles
in a feed, and the "Show more" link between the comments is no article (the separators are hidden
from assistive technology, the "load hidden posts" control of the stream is an entry of its own). The acceptance test `KeyboardCest` (content module) covers the skip link, the
focus after a dialog closes, tooltips on keyboard focus, the comment menu, Enter and Space on a
`data-action-click` element, the focus after a stream entry is deleted, the skip links and the feed
keys in the stream and in the comments. Vitest covers the JS: the focus helper (`ui.focus`), the feed
keys (`ui.feed`), tooltips on focus and Escape, Tab in editor tables, and the comment island's roles,
loading and hand-over.

## Implementation in core

**Today:** the focus ring for every control, with the input modality; the skip links to the
content, the navigation and the sidebar; the stream as a feed of articles with `humhub.ui.feed`, and the
comments of an entry as a feed inside it (the comment island); `<header>`,
`<nav>` and `<aside>` landmarks, and `aria-current="page"` on the active menu link, kept up by
`humhub.ui.navigation` on a pjax swap; `humhub.ui.focus`, which gives the focus back to a dialog's
trigger (or its menu toggle, or the content) and hands it on when a stream entry is removed (the
comment island does the same in its `focusHandOver.js`), while an edited entry keeps it; `humhub.ui.tooltip`, which shows tooltips on keyboard focus
as on hover and hides them on Escape; the comment menu in the tab order, shown while its comment
holds the focus; Enter and Space on a `data-action-click` element that is no link or button; Tab
leaving an editor table; the focus after a pjax swap and after "Show more" in the stream; the Vue
dialog, the pickers, the space chooser and the item browser following the rules; the editor toolbar
with keyboard tests; icon-only buttons deriving their `aria-label` from the tooltip; the create form expanding when the keyboard
focus enters it.

**Gaps:** none known in core. The stream entry's own menu is always visible, so it needs no
hover rule.

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
