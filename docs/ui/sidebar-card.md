# Sidebar Card

> Found in the design: the column beside the stream of the dashboard, a space and a profile
> (latest activities, members, tags, upcoming events, a module's snippet)
> · Core: `humhub\widgets\SidebarCard` (since 1.20); `humhub\widgets\PanelMenu` on a `.panel`
> (deprecated, until 1.21)
> · Technology: **PHP**

A card in the sidebar of a page: the column of `BaseSidebar` widgets beside the stream of the
dashboard, a space or a profile. It shows one thing beside the content (who is a member, what
happened lately, what comes up next) with a short title, and people can collapse the ones they
don't need. It is not the [Sidebar](sidebar.md) of the Enterprise theme, which is the main
navigation on the left.

## Anatomy

| # | Part | Notes |
|---|---|---|
| 1 | **Title** | A short title, the card's `h2`. A title that opens something (the members of a space as a list) is a button. |
| 2 | **⋮ menu** | The card's actions (e.g. "Show as List") and, last, **Collapse** / **Expand**. |
| 3 | **Body** | The content. Collapsed, only the title stays. |

## Behaviour

- **Collapse:** the ⋮ menu's last entry folds the body away and unfolds it again. The browser
  remembers the state per card (`localStorage`), so a collapsed card stays collapsed on the next
  visit. Cards that need action (an incomplete setup, open requests) cannot be collapsed.
- **Order:** the sidebar's widgets in their `sortOrder`. Sorting and hiding cards by the user
  are features for later and belong to this component.
- **Phones:** below `lg` the sidebar is hidden (`_sidebar.scss`).

## Accessibility

- The card is a `<section>` labelled by its `h2` title, so the sidebar's cards are regions with
  headings.
- The ⋮ button has a name ("More actions"); the collapse entry controls the body
  (`aria-controls`, `aria-expanded`), its label says what it will do.

## Markup and styles

Bootstrap's **`.card`** with the look of the [Content Card](card.md) (`.content-card`) and the
modifier **`.sidebar-card`**.

```html
<section id="space-members-panel" class="card content-card sidebar-card members" aria-labelledby="w5-title"
         data-ui-widget="ui.sidebarCard.SidebarCard" data-ui-init>
    <div class="card-header">
        <h2 id="w5-title" class="card-title">Space members (12)</h2>
        <div class="card-header-actions">
            <div class="dropdown">
                <button class="btn btn-ghost btn-icon-only" data-bs-toggle="dropdown" aria-label="More actions">⋮</button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#">Show as List</a></li>
                    <li><a class="dropdown-item sidebar-card-collapse" href="#…-body" data-bs-toggle="collapse"
                           aria-controls="…-body" aria-expanded="true">
                        <span class="sidebar-card-collapse-hide">Collapse</span>
                        <span class="sidebar-card-collapse-show">Expand</span></a></li>
                </ul>
            </div>
        </div>
    </div>
    <div id="…-body" class="card-body collapse show">…</div>
</section>
```

| Class | Element |
|---|---|
| `.card`, `.card-header`, `.card-title`, `.card-body` | Bootstrap |
| `.content-card` | The look of a content card |
| `.sidebar-card` | The sidebar card |
| `.card-header-actions` | The ⋮ menu: a ghost icon button as small as a stream entry's (the sidebar is narrow), not making the header higher |
| `.card-title-action` | A title that opens something |
| `.sidebar-card-collapse` (`-hide`, `-show`) | The collapse entry and its two labels, by Bootstrap's `.collapsed` |
| `.content-card-danger` | A card that needs action |

- **File:** `_sidebar.scss`, behind `$prev-sidebar`; the look is the content card's
  (`_card.scss`).
- **JS:** `humhub.ui.sidebarCard` (`ui.sidebarCard.SidebarCard` on the card) restores and
  remembers the collapsed state; Bootstrap's collapse toggles the body.

## Implementation in core

```php
<?php SidebarCard::begin([
    'title' => Yii::t('SpaceModule.base', 'Space members'),
    'titleAction' => ['data-action-click' => 'ui.modal.load', 'data-action-url' => $url],
    'menu' => [new MenuLink(['label' => Yii::t('SpaceModule.base', 'Show as List'), 'icon' => 'list', 'url' => '#', 'htmlOptions' => $options])],
    'options' => ['id' => 'space-members-panel'],
]) ?>
    …
<?php SidebarCard::end() ?>
```

- **PHP:** `humhub\widgets\SidebarCard` (`begin()` / `end()` around the body): `title`,
  `encodeTitle` (`false` for a translation that still emphasises a word, never with user input),
  `titleTag` (default `h2`), `titleAction` (the attributes of a button around the title),
  `menu` (a `DropdownMenu`, its class or its `MenuEntry` items), `collapsible` (default `true`),
  `collapseId` (the body's id and the key of its remembered state; by default derived from the
  widget whose view renders the card, so two cards of one widget need their own), `options`,
  `bodyOptions`.
- **Where:** every sidebar widget of core: latest activities, popular topics, the tour, new
  spaces, a space's members, followers, tags and the membership, a profile's followers,
  spaces, tags and friends, open member requests and the administrators' warnings.
- **Instead of:** a hand-written `.panel` with `PanelMenu` (`extraMenus` HTML,
  `enableCollapseOption`, `collapseId`). `PanelMenu` and `ui.panel.PanelMenu` keep working for
  a module's panel until 1.21. The remembered state carries over (`pm_<collapseId>`).
