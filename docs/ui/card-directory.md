# Card Directory

> Found in the design: People directory, Spaces directory, Marketplace
> · Core: `CardDirectory.vue`, `CardGrid.vue`, `CardSkeleton.vue` (since 1.20). Used by People,
> Spaces and Marketplace.
> · Technology: **Vue** (exists)

A page that lets people **browse** a collection of people, spaces or modules as cards. It always
has the same structure:

1. a [Page Toolbar](page-toolbar.md) with title and primary action
2. a [Filter Bar](filter-bar.md)
3. an optional notice
4. a responsive grid of [Entity Cards](entity-card.md) that loads page by page

Use it when people **discover and choose**, and every item is worth a picture and a short
description. For administrative lists where people **compare and act on many records**, use the
[Data Table](data-table.md).

![Card directory](images/card-directory.svg)

## Anatomy

| # | Part | Notes |
|---|---|---|
| 1 | **Page toolbar** | Title ("Spaces Directory", "Marketplace") and its actions: one primary action (create space, invite people, update all) plus secondary tools (settings) |
| 2 | **Filter bar** | Search plus the list's filters. Tags and type badges on the cards set filters here. |
| 3 | **Notice** | Optional. Sits between toolbar and grid, e.g. update information in the marketplace. |
| 4 | **Card grid** | Cells at least 264px wide with a 16px gap, as many columns as fit. Cards in a row have equal height, and their footers line up. |
| 5 | **Loading** | Skeleton cards with the card's geometry: on the first load, and below the grid while the next page loads. |
| 6 | **Paging** | The next page loads automatically when the end of the grid scrolls into view. A "Show more" button stays for keyboard users and as a fallback. |

## Behaviour

- **Live filtering.** Every filter change reloads from page 1. A reload that takes longer than a
  moment dims the grid, while a fast answer doesn't flash. Filters are kept in the URL.
- **Cards as filters.** A card's tags ("MARKETING") and type badges ("OFFICIAL") are buttons that
  set the matching filter, with the tooltip "Filter by Marketing".
- **Entrance.** Cards pop in with a short rise, staggered by about 32ms per card. Reduced motion
  turns this off.
- **Per-viewer state.** The list itself is the same for everyone. Each viewer's state (membership,
  following, friendship) is loaded for every page right after it, and until it arrives the card's
  actions show a disabled placeholder. This keeps the list cacheable and fast.
- **Updates in place.** Joining a space or installing a module updates that one card, not the whole
  list.
- **Empty:** one message across the grid: "No results found!" with the hint "Try other keywords
  or remove filters."
- **Error:**
  - when nothing could be loaded, a message with "Try again" replaces the grid
  - when a later page fails, the message sits below the cards that are already loaded, and only
    that page is retried

## Mobile

- The grid fills the width with as many 264px columns as fit, down to **one column** on phones.
- Cards keep their full content; nothing is hidden on small screens.
- The filter bar folds behind the filter button, and the toolbar actions stay visible
  ([Page Toolbar](page-toolbar.md#mobile)).

## Accessibility

- The page title is the `h1`, and each card title is a link.
- Images inside the card are decorative (`aria-hidden`), because the title link names the item.
- "Show more" is a real button, so infinite scrolling never blocks keyboard users.
- Loading and error messages use `role="alert"` and `aria-busy` where they change.

## Implementation in core

**Exists:** `CardDirectory` (`protected/humhub/vue/CardDirectory.vue`). It composes:

- `PageToolbar` with `FilterBar`
- an optional `notice` slot
- `CardGrid`, which feeds page by page from an endpoint answering the API list envelope

Further details:

- **Styles:** `resources/scss/_card-directory.scss`.
- **Documentation:** [Vue.js Components](../develop/ui-js-vuejs-components.md).
- **Consumers:**
  - `user/vue/PeopleDirectory.vue` (`user\widgets\PeopleDirectory`)
  - `space/vue/SpaceDirectory.vue`
  - `marketplace/vue/MarketplaceBrowser.vue`, the reference consumer
- **Per-viewer states:** the `itemStates` prop is `(ids) => Promise<{ [id]: state }>`.
- **The card:** passed through the `card` slot.
- **Instance API:** `reload()`, `replaceItem()` and `replaceState()` for updates in place.

**Gaps compared to the design:**

| Gap | Note |
|---|---|
| Reduced motion | Only the OS setting (`prefers-reduced-motion`) is respected. A user setting needs a shared root attribute. |
| Notice | The slot exists, but there is no shared notice component. Define one (info / warning) for directories. |

## Open questions

1. **Sort:** the People and Spaces designs offer "Sort" as a filter field. Is sorting a filter
   (select in the bar) or a separate control?
2. **Empty state with action:** should an empty directory offer its primary action (e.g. "Create
   the first space")?
3. **Other directories:** which further lists become card directories (groups, topics, module
   pages such as wiki overviews)?
