# Module Migration — Version 1.21

Breaking changes, new APIs and deprecations of the 1.21 release cycle.

- **The Bootstrap 3 panels are removed** (deprecated in 1.20, see "Core renders content cards instead of Bootstrap 3
  panels" and "A sidebar widget is a `humhub\widgets\SidebarCard`" in [Version 1.20](module-migrate-1.20.md)).
  - **Removed:** `_bootstrap3.scss` with its `$prev-bootstrap3` switch: the styles of `.panel`, `.panel-heading`,
    `.panel-title`, `.panel-body`, `.panel-danger`, `.btn-panel-title-action` and `.well`. A module or theme that still
    renders them gets unstyled boxes; it uses the card markup instead:

    | Panel | Card |
    |---|---|
    | `.panel.panel-default` | `.card.content-card` |
    | `.panel-heading` | `.card-header` with the title in `.card-title` |
    | `.panel-body`, `.panel-footer` | `.card-body`, `.card-footer` |
    | `.panel-danger` | `.card.content-card.content-card-danger` |
    | `.btn-panel-title-action` | `.card-title-action` |

    A page renders `humhub\widgets\ContentCard`, a sidebar widget `humhub\widgets\SidebarCard`.
  - **Removed:** `humhub\widgets\PanelMenu` with its view and the JS widget `ui.panel.PanelMenu`
    (`humhub.ui.panel.js`, the `ui.panel` config of `CoreJsConfig`). A sidebar widget renders a `SidebarCard`: its
    `menu` takes the entries of `extraMenus` (as `MenuLink`s), its `collapseId` is `PanelMenu`'s, so a remembered
    collapsed state carries over.
  - **Also removed:** the panel variants of core rules: the left navigation's list group only inside
    `.left-navigation` (no longer in any `.panel`), a `.panel-heading` in a theme's own `left-navigation.php`, a
    `.panel-body` or `.panel-heading` of a module view inside a section layout's card, the mobile gap between panels,
    the panel box of the login and installer pages, `.panel-body > .text-body-secondary`.
  - **Kept:** `.img-responsive` for images in older user content (`_markdown.scss`), `.loader` for the rich text
    editor's image placeholder (`_richtext.scss`), the legacy tabs (`.tab-menu`) in a card body running to its edges
    (`_nav.scss`), and the hook classes of the space and profile header (`.panel-profile*`).
  - Stored content with panel markup (e.g. custom pages created with the panel wrapper of their editor or from their
    old layout templates) needs a migration in its module.
