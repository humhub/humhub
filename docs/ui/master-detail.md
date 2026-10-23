# Master / Detail & Section Menu

> Found in the design: Administration (section menu + page), Messages (conversations + thread),
> Notifications (list + filters)
> · Core: `admin\widgets\AdminMenu` (`LeftNavigation`); no master/detail layout yet
> · Technology: **Both** (proposed). It is a CSS layout with a small JS helper: PHP pages (the
> administration) and Vue islands (messages, notifications) use the same classes.

A two-pane layout: a fixed-width **side pane** next to a flexible **main pane**. When there isn't
enough room for both, it **folds into one pane** at a time, with a back arrow to return. This is
how the new design expresses **hierarchy on mobile**: menu → page, list → detail,
content → filters. There is no hamburger drawer.

The **section menu** is its most important use. The administration's menu sits in the side pane,
and the chosen section's page in the main pane.

![Master/detail variants](images/master-detail-variants.svg)

## Variants

| Variant | Side pane | Use |
|---|---|---|
| **Slim** | 280px, at the start | A section menu next to its pages (Administration; later account and space settings) |
| **Default** | 400px, at the start | A list next to the selected item (Messages: conversations + thread) |
| **Side at end** | 400px, at the end | Main content with a supporting pane (Notifications: list + filters) |

## Section menu

- A card with the area's title ("Administration") and one entry per **section**: Overview,
  People, Spaces, Content, Communication, Security, Appearance, Integrations, Modules, System.
- **Sub-pages are not nested in the menu.** They are [Pill Tabs](tabs.md) in the section's
  [Page Toolbar](page-toolbar.md).
- The **active entry** is filled dark.
- **Count badges** show what waits inside a section, as the **sum** of its sub-pages: People ⑦ =
  4 invitations + 3 approvals. The tabs inside show only dots
  ([Badges & Status](badges.md#count-badges-one-number-per-place)).
- The menu is **sticky** and one screen tall. It scrolls inside itself only when the window is
  shorter than its entries.
- Use a section menu for areas with about 5–12 sections. Fewer sections get tabs only.

## Behaviour

- **The fold depends on the layout's own width, not the viewport.** Below **816px** (two
  400px panes plus the gap) it shows one pane. This also happens on a narrow desktop window, or
  when other columns take room.
- **Folded:**
  - the side pane (menu, list) is the start
  - opening an entry shows the main pane, with a **back arrow** before its title
  - the arrow only exists while folded
- **Real URLs.** In the administration, `/admin` shows the menu and `/admin/people` shows the page,
  so the browser's back button and shared links work. Messages and notifications switch panes
  inside the page.
- **Page swaps in place** (administration): choosing a section or tab replaces only the page card.
  The old content dims only if the answer takes longer than 300ms. Every URL still works as a
  full page load.
- **Unsaved changes:** leaving a [Settings Page](settings-page.md) with unsaved changes asks
  first, also on in-place navigation.
- **Motion:** the arriving pane slides in 8px from the side it comes from. Reduced motion turns
  this off.
- **Full height:** both panes fill the window below the top bar and scroll inside themselves, so
  the list doesn't scroll away while reading a thread.

![Master/detail on mobile](images/master-detail-mobile.svg)

## Mobile

| Width of the layout | Shows |
|---|---|
| ≥ 816px | Both panes side by side. The back arrow is hidden. |
| < 816px | One pane. The side pane is the start, and the main pane comes with a back arrow. |

## Accessibility

- The section menu is a `<nav>` labelled by its title. The active entry has
  `aria-current="page"`.
- Counts carry hidden text ("7 waiting").
- Hidden panes are `display: none`, so they are removed from the tab order and from screen
  readers.
- The back arrow is a real link where a URL exists, labelled "Back to Administration" or "Back to
  conversations".
- After a pane change, focus moves to the arriving pane's title. After an in-place page swap,
  focus stays on the pressed element if it is still there.

## Implementation in core

**Today:**

- **Administration menu:** `admin\widgets\AdminMenu` extends `humhub\widgets\menu\LeftNavigation`
  (template `widgets/menu/views/left-navigation.php`, a Bootstrap panel). On small screens it
  stacks above the content.
- **Counts:** `Badge::danger()` concatenated into labels (`AdminMenu`, `UserMenu`).
- **Admin layout:** `modules/admin/views/layouts/main.php`, with full page loads (pjax optional).
- **Lists with details:** `NotificationOverview.vue` uses two Bootstrap panels. The messages UI is
  in the mail module, with its own columns.

**Proposal:**

- **Layout:** a `PaneSplit` layout for PHP views and a `PaneSplit` Vue component, rendering the
  same classes. The fold is pure CSS (container query). A tiny JS helper answers "is it folded?"
  for code that must decide whether opening an entry also switches panes. That code must never
  check the window width.
- **Section menu:** a new `SectionMenu` base (vertical card, count badges from data, `homeUrl` for
  the folded start page). `AdminMenu` moves onto it. Its entries keep the `MenuLink` API and get a
  `badge` instead of label HTML.
- **Admin regrouping:** the design groups today's admin menu into the sections listed above.
  That is a separate change, but the section menu makes it possible.

```php
// admin layout
PaneSplit::begin(['variant' => PaneSplit::VARIANT_SLIM, 'showMain' => !$isAdminHome]);
    echo AdminMenu::widget();                       // side pane
PaneSplit::main();
    echo PageToolbar::widget([
        'title' => $sectionTitle,
        'tabs' => $sectionTabMenu,
        'back' => ['url' => ['/admin'], 'label' => Yii::t('AdminModule.base', 'Administration')],
    ]);
    echo $content;                                  // main pane
PaneSplit::end();
```

```html
<!-- Vue -->
<PaneSplit :show-main="openConversationId !== null">
    <template #side><ConversationList @open="open" /></template>
    <template #main><ConversationThread :id="openConversationId" @back="close" /></template>
</PaneSplit>
```

## Open questions

1. **In-place swap:** do we swap only the page card in the administration (fetch, or pjax), or
   keep full page loads? Full loads are simpler and already work with real URLs.
2. **History when folding:** should opening a conversation on a phone add a history entry, so the
   device's back button returns to the list? The administration does through its URLs, but
   messages doesn't.
3. **Admin regrouping:** adopt the new section grouping (People, Spaces, Content, Communication,
   Security, Appearance, Integrations, Modules, System) together with the new menu, or
   separately?
4. **Other areas:** account settings and space settings: section menu, or tabs only?
