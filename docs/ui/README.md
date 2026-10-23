# HumHub UI Styleguide

This folder describes the UI components and mechanics of the upcoming HumHub design. It is the
shared reference from which core components are built, and which module developers rely on.

The visual reference is the designer's interactive prototype of the new design. The prototype's
CSS, icons and markup are **not** the implementation template. These pages describe what a
component looks like, how it behaves and how it maps to core. Each page has illustrations in
[`images/`](images/).

Since 1.20, parts of the design already exist in core as Vue components: `PageToolbar`,
`FilterBar`, `ViewSwitch`, `CardDirectory`, the item browser kit and others (see
[Vue.js Components](../develop/ui-js-vuejs-components.md)). For those, the *Implementation in core*
section of each page lists the gaps between the design and the current implementation.

**Start here:** [Principles](principles.md). These are the rules every component follows: hide,
don't disable; one main action per header; what the colours mean; how things fold on mobile.

## Where things live

![Where the components live on a page](images/overview-map.svg)

## Components by category

### Layout & navigation

How a page is framed and how people move between pages.

| Component | What it is | Status | Technology |
|---|---|---|---|
| [Page Toolbar](page-toolbar.md) | The card at the top of a page: title, actions, filters or tabs | in review | Both (proposed) |
| [Pill Tabs](tabs.md) | Switch between sibling pages or views | in review | Both (proposed) |
| [Master / Detail & Section Menu](master-detail.md) | Side pane + main pane that folds on mobile; the administration menu | in review | Both (proposed) |
| [Mobile Navigation](mobile-navigation.md) | Bottom bar, menu sheet and widget drawer on phones | in review | PHP (proposed) |

### Basic components

Small building blocks used everywhere.

| Component | What it is | Status | Technology |
|---|---|---|---|
| [Buttons](buttons.md) | Variants, icon buttons, full width, choice rows, header actions | in review | Both |
| [Badges & Status](badges.md) | Chips, priority, tags, count badges, dots | in review | Both |
| [Accordion](accordion.md) | Groups that open in place, with unsaved-changes tracking | in review | Both (proposed) |
| [Dialog](dialog.md) | Confirm, form, tabbed and multi-step dialogs | in review | Both |
| [Context Menu](context-menu.md) | The ⋮ menu for one item or area | in review | Both |

### Collections

Showing, filtering and acting on many items.

| Component | What it is | Status | Technology |
|---|---|---|---|
| [Filter Bar](filter-bar.md) | Search and filters that apply live | in review | Vue (exists) |
| [Data Table](data-table.md) | Records to compare and act on, with bulk actions | in review | Vue |
| [Card Directory](card-directory.md) | Browse people, spaces, modules as cards | in review | Vue (exists) |
| [Entity Card](entity-card.md) | The card for a person, space or module | in review | Vue (exists) |
| [Item Browser](item-browser.md) | Browse levels of items: folders and files | in review | Vue (exists) |

### Page patterns

How components combine into a whole page.

| Pattern | What it is | Status | Technology |
|---|---|---|---|
| [Settings Page](settings-page.md) | Settings groups, controls with defaults, explicit Save/Reset | in review | Both (proposed) |

## Which one do I use?

| I want to… | Use |
|---|---|
| …switch between sibling pages of a section | [Pill Tabs](tabs.md) (primary) |
| …switch between sections of one dialog or card | [Pill Tabs](tabs.md) (secondary) |
| …show the same data differently (grid / list, board / list) | View switch (see [Item Browser](item-browser.md)) |
| …give an area with 5–12 sections a menu | [Section Menu](master-detail.md#section-menu) |
| …show records people compare and act on in bulk | [Data Table](data-table.md) |
| …let people browse and pick people, spaces or modules | [Card Directory](card-directory.md) |
| …let people browse levels (folders) | [Item Browser](item-browser.md) |
| …show a list next to the selected item | [Master / Detail](master-detail.md) |
| …ask for a decision or a short task | [Dialog](dialog.md) |
| …offer more actions for one item | [Context Menu](context-menu.md) |
| …structure a long settings page | [Settings Page](settings-page.md) with [Accordions](accordion.md) |
| …mark a state or a fact | Chip ([Badges & Status](badges.md)) |
| …show that something waits | Count badge on the menu entry, dot on the page ([Badges & Status](badges.md)) |

## Not documented yet

These appear in the design but have no page yet. They follow when their implementation starts:

- **Layout:** top bar (search, message and notification drawers, user menu), sidebar (main nav,
  space categories), space and profile header
- **Forms:** form fields and validation, select and pickers, date picker, switch and checkbox,
  file upload
- **Feedback:** toast, tooltip, hover card, skeleton loading, empty states
- **Stream:** wall entry, composer, comments, topic chips, embedded media
- **Module views:** tasks board, calendar, messages, notifications, marketplace dialogs

## Conventions

Technology is decided per component:

- **Vue:** a Vue component, for use inside Vue islands only.
- **PHP:** a PHP widget with server-rendered markup.
- **Both:** one design and one set of CSS classes, with a Vue component and a PHP widget that
  render identical markup.

Status values:

- **draft:** taken over from the prototype analysis, not discussed yet.
- **in review:** rewritten, open questions listed for discussion.
- **agreed:** ready for implementation.

Every component page has the same sections:

- a header stating where the component was found in the design, what exists in core, and the
  technology
- illustrations, then anatomy, variants, behaviour, mobile and accessibility
- *Implementation in core* (what exists, the gaps, an API sketch) and *Open questions*

New pages follow this structure.
