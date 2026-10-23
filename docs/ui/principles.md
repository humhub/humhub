# Principles

Rules that apply across components. Each component page refines them, but should never
contradict them. When a new component is designed or built, check it against this list first.

## Actions

1. **Hide, don't disable.** A control that cannot act right now is not shown. Save appears after
   a change, Reset while something differs from the default, "Update all" while updates exist,
   and a menu only lists allowed actions. Disabled is only for short moments (a request is
   running, state is still loading) and for values fixed by configuration, which then say why.
2. **One main action per header.** A page toolbar or card header has at most **one** accent
   button (create, add, invite). The order is always secondary tools → the accent action → ⋮
   overflow. See [Page Toolbar](page-toolbar.md#header-actions).
3. **Buttons have one job each.** Primary confirms, accent creates or marks the chosen option,
   secondary is neutral, and danger is the last step of something destructive. See
   [Buttons](buttons.md).
4. **Destructive steps confirm, and say what they do.** The dialog's title asks the question
   ("Leave Marketing?"), and the button names the action ("Leave"), never "OK".
5. **Icon-only means labelled.** Every icon button has an accessible name and a tooltip with the
   same text, shown on hover and on keyboard focus.

## State and feedback

6. **Changes apply live, in place.**
   - Filters, sorting and paging update the content without a page reload.
   - The URL reflects the state, so it can be shared and bookmarked.
   - Rows and cards change individually instead of reloading the whole list.
7. **Settings save explicitly.** Changed groups show an accent dot, Save and Reset appear, and
   leaving with unsaved changes asks first. Autosave only for personal pages that consist only of
   switches, and never mixed on one page. See [Settings Page](settings-page.md).
8. **Show the default, and say why.** Settings state their default ("Default: off"). A value that
   can't be changed says why ("Set in the configuration file.").
9. **Loading keeps the shape.** Skeletons have the geometry of the final content, so nothing
   jumps. Content that reloads is dimmed only after a short delay (about 150–300ms), so fast
   answers don't flicker.
10. **Errors stay where they happened.** A field error sits at the field, a failed row action
    leaves the row unchanged and shows a toast, and a failed page offers "Try again".

## Colour and signals

11. **Colours carry meaning, from a closed set.** The tones are success, warning, danger, info
    and neutral, and nothing else. **A state that was chosen is neutral, not an error** (Disabled,
    Archived). See [Badges & Status](badges.md).
12. **Red means waiting, unread or error. Accent means create, chosen or unsaved.** Both keep
    these meanings everywhere: in dots, badges, buttons and rings.
13. **One number per place.** A count sits on the entry that leads into a section, as the sum of
    what waits inside. The pages inside only show dots. Counts are never 0.
14. **Colour is never the only signal.** Every dot and every coloured state has text, visible or
    hidden for screen readers.

## Layout and responsiveness

15. **Components fold by their own width, not the viewport.** Container queries decide, so a
    component behaves the same on a phone, in a narrow window or next to a widget column.

    | Width | Change |
    |---|---|
    | 768px | App shell: desktop navigation → bottom bar ([Mobile Navigation](mobile-navigation.md)) |
    | 816px | Master/detail: one pane ([Master / Detail](master-detail.md)) |
    | 1000px / 570px | Filter bar: 4 → 2 → 1 per row, then fold ([Filter Bar](filter-bar.md)) |
    | per column | Data table: columns fold by priority ([Data Table](data-table.md)) |

16. **The page never scrolls sideways.**
    - Table columns fold into expandable rows.
    - Tab strips scroll inside themselves with fading edges.
    - Wide content (a matrix) scrolls inside its own box.
17. **Hierarchy on mobile is a back arrow, not a drawer.** Menu → page and list → detail fold
    into one pane with a back arrow. Everything else is in the bottom bar and its menu sheet.
18. **Every gesture has an alternative.** Long press, swipe and drag-and-drop are shortcuts. The
    same action is always reachable by tap, click or keyboard.

## Motion

19. **Short and purposeful.** Most transitions take about 200ms. Things enter where they come
    from: a deeper level from the right, new rows from above, leaving rows to the side.
20. **Reduced motion is respected.** With the system setting (and later a user setting), all
    movement becomes an instant change. Nothing depends on animation to be understood.

## Building components

21. **Technology per component.** Each component is Vue, PHP or Both. "Both" means one design and
    one set of CSS classes, with a Vue component and a PHP widget rendering identical markup.
22. **Extend, don't override.** Modules add menu entries, card actions, filter types, columns and
    slots through the extension points (menus, `registerMenuEntry()`, `registerSlotComponent()`,
    list events). They never copy and change a core view.
23. **Tokens, not values.** Colours, spacing, radii and shadows come from theme variables, so
    themes and dark mode work without touching components.
24. **Accessibility is part of the component.** Labels, roles, focus handling and hidden texts
    are specified on each page and are not optional.
