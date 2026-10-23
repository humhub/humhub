# Context Menu

> Found in the design: ⋮ on table rows, cards, wall entries, comments, messages, files; header
> overflow menus; right-click on files
> · Core: `humhub\widgets\menu\DropdownMenu` (PHP), `DropdownMenu.vue` (Vue, since 1.20)
> · Technology: **Both**

A short list of actions for **one item** or **one area**, opened from a ⋮ button (or by
right-clicking an item). It keeps rows, cards and headers calm: only the most important action
is visible, and everything else is in the menu.

![Context menu](images/context-menu.svg)

## Anatomy

| # | Part | Notes |
|---|---|---|
| 1 | **Trigger** | ⋮ ghost [icon button](buttons.md) with the label "More actions for Emma Williams". It shows a pressed look while open. |
| 2 | **Rows** | Optional icon plus label. The whole row is the target. |
| 3 | **Separators** | Between groups of related actions (edit / state / delete) |
| 4 | **Count** | Optional count badge on a row (e.g. 3 approvals waiting) |
| 5 | **Destructive rows** | Last, in red. They always confirm in a [Dialog](dialog.md). |

## Rules

- **Order:** frequent actions first, then state changes (Disable, Archive), then destructive
  actions last.
- **Labels are verbs:** "Edit", "Move to…", "Delete". A trailing "…" means a dialog follows that
  asks for more input.
- **Only allowed actions appear.** An action the viewer can't take is left out, not shown
  disabled.
- **One item, one menu.** For actions on several selected items, the selection menu takes over
  (see [Data Table](data-table.md#selection-and-bulk-actions)).
- If an item has **exactly one** action, show it as an icon button instead of a menu with one row.

## Behaviour

- **Placement:** below the trigger with the right edges aligned. When there is no room below, it
  opens above. When it is still too tall, it scrolls inside itself.
- **At the pointer:** right-click, or the context-menu key, on an item that supports it (files,
  tiles) opens the same menu at the pointer.
- **Closing:** a click outside, Escape, choosing a row, scrolling the page, or a change of the
  window width.
- **Motion:** a short fade and rise (about 110ms). Reduced motion turns it off.
- **Extensible:** modules add rows to an item's menu without touching the item (menu entries,
  sort order).

## Mobile

- The same anchored menu, **no action sheet**. It stays above the on-screen keyboard.
- Rows are at least 40px high for touch.
- Long-press on an item can open the same actions (where long-press is supported).

## Accessibility

- Trigger: `aria-haspopup="menu"` and `aria-expanded`, with a label that includes the item's name.
- Menu: `role="menu"` with `menuitem` rows.
  - Arrow keys move between rows and Home/End jump.
  - Typing a letter jumps to the next row starting with it (proposal; Bootstrap doesn't do this).
  - Escape closes the menu and returns focus to the trigger.
- Destructive rows say so in text ("Delete"). Colour is not the only signal.
- A count on a row is part of its accessible name ("Approvals, 3 waiting").

## Implementation in core

**Today:**

- **PHP:** `humhub\widgets\menu\DropdownMenu` (Bootstrap dropdown with `MenuEntry` / `MenuLink`
  entries), used e.g. by `WallEntryControls` and `PanelMenu`.
- **Vue:** `DropdownMenu.vue` with a free slot or data-driven entries (`menuId`, `entries`),
  extensible through `registerMenuEntry()` / `removeMenuEntry()`. It opens at the pointer via
  `open(event)` for right-click, and uses Bootstrap's dropdown JS for toggling and keyboard
  handling.

**Gaps:**

| Gap | Proposal |
|---|---|
| Look: Bootstrap dropdown | New row style (icon column, separators, counts, red destructive rows) in both PHP and Vue |
| No count on rows | `MenuLink` `badge` (shared with the [Badges & Status](badges.md) proposal) |
| No "destructive" flag | `MenuLink` `destructive` → red, sorted last, confirm required |
| Placement | Flip above and scroll inside when there is no room. Check what Bootstrap/Popper already does, and fill the gaps. |
| Single action as icon button | A helper that renders an icon button instead of a menu when there is exactly one entry |

## Open questions

1. **Trigger icon:** ⋮ (vertical) everywhere, or ⋯ in some places? The design uses ⋮.
2. **Action sheets on phones:** stay with anchored menus (design), or switch to bottom sheets on
   touch devices?
3. **Disabled rows:** never shown (rule above), or shown with a reason in some cases (e.g. "Delete
   — only the owner can delete")?
