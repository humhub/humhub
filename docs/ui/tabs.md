# Pill Tabs

> Found in the design: Admin › People (section tabs), space pages (tab strip), Notifications (All / Unread / Read), component demo page
> · Technology: **Both** (proposed). Link tabs come from PHP menus (`TabMenu`). Panel tabs are also
> needed inside Vue islands (e.g. Notifications All / Unread / Read).

A single horizontal strip of pill-shaped tabs. It switches between sibling pages or sibling views
of the same surface, for example:

- a space's Stream, Calendar, Files and Wiki
- the sub-pages of an admin section (Users, Invitations, Approvals, …)
- the sections of a dialog
- a list's All / Unread / Read

Pill Tabs replace every tab style in use today: Bootstrap `nav-tabs`, the `TabMenu` look and
`SettingsTabs`.

Don't use Pill Tabs for:

- **Different representations of the same data** (board/list, month/week): use the view switch.
- **A vertical section menu** (the admin's left menu): use the secondary navigation.

![Pill tab variants](images/tabs-variants.svg)

## Variants

| Variant | Where | Look |
|---|---|---|
| **Primary** | Directly on the page: space and profile tabs, admin section tabs. Usually sits in the page header card. | Outlined pills. The active pill is filled and has a darker outline. |
| **Secondary** | Inside a card, panel or dialog. This is the default inside dialogs. | No outlines. The active pill is filled; hover gives a light fill. |

Both variants share the same pill types, behaviour and markup. Only the styling differs.

## Pill types

| Type | Example | Notes |
|---|---|---|
| Text | Users, Groups | |
| Icon + text | Stream, Calendar, Files | Leading icon with a 4px gap. Use icons on all pills of a strip or on none. |
| With **waiting dot** (red) | Invitations •, Approvals • | Something on that page needs action. The **count** belongs to the menu entry that leads to the section (e.g. "People ⑦"). A tab only shows the dot. Screen readers get hidden text such as "3 waiting". |
| With **unsaved dot** (accent) | Moderator • | That tab has unsaved changes (permission role tabs). |
| Collapsible | Create Task › Schedule | A pill that appears or disappears with an animation when a related option is switched on or off. A collapsed pill is removed from the tab order. |

The two dot colours always mean the same thing: **red = waiting/unread, accent = unsaved**.

## Behaviour

- **Never wraps.** When the pills don't fit, the strip scrolls horizontally with a hidden scrollbar.
  A 32px fade marks each edge that has more pills behind it. The fade only appears on that edge,
  and is recalculated on scroll and on resize.
- **A pressed pill scrolls to the centre**, so its neighbours become visible. The scroll is smooth
  and stops at the ends of the strip. When nothing overflows, nothing moves.
- **Two kinds of tabs:**
  - **Link tabs** navigate to a URL. They are used for admin sub-pages and space/profile pages, and
    the active pill reflects the current route. In the administration the prototype swaps only the
    page card and keeps the strip in place. The old content is dimmed only if the response takes
    longer than 300ms.
  - **Panel tabs** switch content on the same page without a request. They are used in dialogs and
    for filters like All / Unread / Read. The panel fades in with a 4px rise, and the container
    height animates (about 140ms).
- **Pill size:** 40px high and fully rounded, with 16px horizontal padding. Pills keep their
  natural width and are never squeezed.

## Mobile

![Pill tabs on mobile](images/tabs-mobile.svg)

- No breakpoint of its own: on narrow screens the strip simply scrolls, with fades and the
  centring behaviour described above.
- When the page header is part of a folded master/detail layout (admin, messages), a **back arrow**
  appears before the title. The tabs stay below it.
- Pills never shrink to icon-only.

## Accessibility

- **Link tabs:** `<nav aria-label="…">` containing links. The active link has `aria-current="page"`.
- **Panel tabs:** a group of buttons with `aria-pressed`. Alternatively use the WAI-ARIA tabs
  pattern (`role="tablist"`/`tab`/`tabpanel` with arrow-key navigation); see Open questions.
- Dots carry visually hidden text such as "3 waiting" or "unsaved changes".
- Focus ring: visible on keyboard focus and never clipped by the scroller.
- Reduced motion: no panel fade, and the centring scroll is instant.

## Implementation in core

| Today | Becomes |
|---|---|
| `humhub\widgets\menu\TabMenu` / `SubTabMenu`: link tabs, about 16 subclasses (`admin\widgets\UserMenu`, `SpaceMenu`, `AuthenticationMenu`, `user\widgets\AccountSettingsMenu`, `space\modules\manage\widgets\DefaultMenu`, …) | Primary Pill Tabs (link tabs). The entry API stays the same; only the render template changes. |
| `humhub\widgets\bootstrap\Tabs`, `FormTabs`, `SettingsTabs` | Panel tabs (secondary by default) |
| Counts as `Badge::danger()` concatenated into the label (`admin\widgets\UserMenu`, `AdminMenu`) | A dot indicator set on the menu entry. The number moves to the parent menu entry. |

**Proposal: API sketch**

```php
// Link tabs: existing TabMenu subclasses keep working; the new template renders pills
new MenuLink([
    'label' => Yii::t('AdminModule.base', 'Approvals'),
    'url' => ['/admin/approval'],
    'indicator' => MenuLink::INDICATOR_WAITING,   // red dot
    'indicatorLabel' => Yii::t('AdminModule.base', '{n} waiting', ['n' => 3]),
]);

// Panel tabs
Tabs::widget([
    'variant' => Tabs::VARIANT_SECONDARY,
    'items' => [
        ['label' => 'All', 'icon' => 'inbox', 'content' => $all, 'active' => true],
        ['label' => 'Unread', 'icon' => 'bell', 'content' => $unread],
    ],
]);
```

JS: one small `humhub.ui.tabs` module for overflow fades, centring and panel switching. It is
bound by delegation, so tabs loaded later via pjax or into a modal need no init call.

## Open questions

1. **Panel tabs semantics:** keep toggle buttons (`aria-pressed`, as in the prototype) or adopt
   the full WAI-ARIA tabs pattern with arrow keys?
2. **Admin in-place navigation:** the prototype swaps only the page card. Do we do the same via
   pjax, or keep full page loads?
3. **Indicator API:** should `MenuLink` get a generic `indicator` (dot) next to a `badge` (count),
   so the same entry can show a count in the section menu and a dot in the tabs?
4. **Space tabs:** in the prototype the space's tabs switch client-side and keep the state in the
   URL hash. In core every tab stays its own route. Confirm.
