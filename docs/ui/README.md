# HumHub UI Components

This folder describes new UI components and mechanics from the upcoming HumHub design concept.
The goal is a shared reference from which core components are built and which module developers
can rely on.

The visual reference is the designer's interactive prototype of the new design. The prototype's
CSS, icons and markup are **not** the implementation template. These pages describe what a
component looks like, how it behaves and how it maps to core. Each page has illustrations in
[`images/`](images/).

Since 1.20, parts of the design already exist in core as Vue components: `PageToolbar`,
`FilterBar`, `ViewSwitch`, `CardDirectory`, the item browser kit and others (see
[Vue.js Components](../develop/ui-js-vuejs-components.md)). For those, the *Implementation in core*
section of each page lists the gaps between the design and the current implementation.

## Components

| Component | Status | Technology |
|---|---|---|
| [Pill Tabs](tabs.md) | in review | Both (proposed) |
| [Page Toolbar](page-toolbar.md) | in review | Both (proposed) |
| [Data Table](data-table.md) | in review | Vue |
| [Filter Bar](filter-bar.md) | in review | Vue (exists) |
| [Accordion](accordion.md) | in review | Both (proposed) |
| [Mobile Navigation](mobile-navigation.md) | in review | PHP (proposed) |
| [Card Directory](card-directory.md) | in review | Vue (exists) |
| [Entity Card](entity-card.md) | in review | Vue (exists) |
| [Badges & Status](badges.md) | in review | Both |
| [Buttons](buttons.md) | in review | Both |
| [Settings Page](settings-page.md) | in review | Both (proposed) |
| [Master / Detail & Section Menu](master-detail.md) | in review | Both (proposed) |
| [Dialog](dialog.md) | in review | Both |
| [Context Menu](context-menu.md) | in review | Both |
| [Item Browser](item-browser.md) | in review | Vue (exists) |

Technology is decided per component:

- **Vue:** a Vue component, for use inside Vue islands only.
- **PHP:** a PHP widget with server-rendered markup.
- **Both:** one design and one set of CSS classes, with a Vue component and a PHP widget that
  render identical markup.

Status values:

- **draft:** taken over from the prototype analysis, not discussed yet.
- **in review:** rewritten, open questions listed for discussion.
- **agreed:** ready for implementation.
