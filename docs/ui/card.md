# Content Card

> Found in the design: below the page header on every page: the board on Tasks, the grid on Files,
> the users table on Admin › People, the key figures on Admin › Overview, settings pages
> · Core: `humhub\widgets\ContentCard`, `ContentCard.vue` (since 1.20); `.panel` (Bootstrap 3
> compatibility) in older views
> · Technology: **Both**

The card that holds a page's content. A page has one [Page Header](page-header.md) and below it one
or more cards. Everything that is not the page's title, page actions, filters or tabs sits in a
card: boards, grids, tables, lists, forms, key figures.

## Anatomy

| # | Part | Notes |
|---|---|---|
| 1 | **Header** | Optional. Either a title (`h2`) with actions, or the content navigation (‹ › period navigation, path bar) with the view switch at the far right. |
| 2 | **Filter Bar** | Optional, below the header, when the page header has tabs and the card's content needs filters (Admin › People › Users). |
| 3 | **Body** | The content. |
| 4 | **Footer** | Optional: paging, counts, "Show more". |

## Header actions

The card header uses the same actions and order as the
[page header](page-header.md#actions): tools, at most one primary action, then ⋮. The difference
is the look of the tools: **ghost** in a card header, **secondary** in the page header.

| | Page header | Card header |
|---|---|---|
| Is a card of its own | yes | no, it is the top of a card |
| Heading | `h1`, exactly one per page | `h2`, several per page possible |
| Pill Tabs | yes | no |
| Back arrow | yes | no |
| Content navigation (‹ ›, path bar, view switch) | no | yes |
| Tools | secondary | ghost |

## Behaviour

- **Full-height module pages** (board, calendar, files): the card fills the remaining window
  height below the page header, and resizes while the page header's filter bar opens or closes.
- **Width:** at most 1440px and centred, aligned with the page header.

## Accessibility

- A card with a title is a `<section>` labelled by its title (`aria-labelledby`).
- Header actions follow the rules of the [page header](page-header.md#accessibility).

## Markup and styles

Bootstrap's **`.card`** with the modifier **`.content-card`**. Class names and SCSS follow
[Styling with Bootstrap 5](bootstrap.md).

```html
<section class="card content-card" aria-labelledby="w3-title">
    <div class="card-header">
        <h2 id="w3-title" class="card-title">Users</h2>
        <div class="card-header-actions">…</div>
    </div>
    <div class="card-body">…</div>
    <div class="card-footer">…</div>
</section>
```

| Class | Element |
|---|---|
| `.card`, `.card-header`, `.card-title`, `.card-body`, `.card-footer` | Bootstrap |
| `.content-card` | The content card's look, set as the card's `--bs-card-*` variables |
| `.card-header-actions` | The actions and the ⋮ menu, right-aligned |

- A card without title is a `<div>`; without title, header content and actions it has no header.
- **Look:** `.content-card` sets the card's `--bs-card-*` variables: no border,
  `$content-card-border-radius` (`$border-radius-lg`), the shadow of the page header
  (`$content-card-box-shadow`, `$content-card-box-shadow-dark`), `$content-card-bg`
  (`--hh-background-color-main`), a transparent header. Padding stays Bootstrap's.
- **Why a modifier, not Bootstrap's `$card-*`:** several modules (survey, ecommerce, matchmaking,
  classified-space, …), themes and the old card grid (`.container-cards .card`) use a plain
  `.card` and expect Bootstrap's look. Changing `$card-*` globally would restyle all of them.
- **File:** `_card.scss`, behind `$prev-card`, with `$content-card-header-gap`,
  `$content-card-title-font-size` and `$content-card-title-font-weight`.

## Implementation in core

**In core (1.20):**

- **PHP:** `humhub\widgets\ContentCard` (`begin()` / `end()` around the content; `title`,
  `titleTag` (default `h2`), `actions`, `menu`, `footer`, `options`).
- **Vue:** `ContentCard.vue` (`<content-card>`): props `title`, `titleTag`, `actions`, `menu`;
  slots `header` (e.g. the content navigation of a card without title), `actions`, default (the
  body) and `footer`.
- **Actions:** `humhub\widgets\menu\CardHeaderActions` with ghost tools. It shares its base
  `HeaderActions` with [`PageHeaderActions`](page-header.md#class-structure); a concrete menu is
  named after its card plus `CardActions` (`AdminUserCardActions`). The ⋮ menu is a
  `DropdownMenu`, as in the page header.
- **First consumer:** the cfiles browser's lower card (`.cfiles-browser__card.card.content-card`).

The component is called `ContentCard`, not `Card`: a Vue component's tag needs a dash
(`<content-card>`), and the name keeps it apart from the [Entity Card](entity-card.md).

```php
<?php ContentCard::begin([
    'title' => Yii::t('AdminModule.user', 'Users'),
    'actions' => AdminUserCardActions::class,    // extends CardHeaderActions
]) ?>
    …
<?php ContentCard::end() ?>
```

```html
<!-- Vue -->
<content-card :title="t('Users')" :actions="actions">…</content-card>
```

**Open:**

| Today | Becomes |
|---|---|
| `.panel.panel-default` with `.panel-heading` and `.panel-body`, written by hand in views (about 66 files in core), styled by `_bootstrap3.scss` | `ContentCard`, step by step with the page header. The administration's section layouts keep a `.panel` for now, since module views rendered into them bring their own `.panel-body`. |
| `humhub\widgets\PanelMenu`: a ⋮ menu with a collapse option in panel headings | The ⋮ `DropdownMenu` of the card header |
| Headings with `<strong>` for the emphasised word ("**User** administration") | Plain titles |

## Open questions

1. **`PanelMenu`:** its collapse option (fold a panel) is not in the design. Keep it as an
   optional card feature, or drop it?
2. **Card without header:** the design has cards that are only content (the Tasks board). Is a
   header-less card the default, or should every card have a title for accessibility (visually
   hidden if needed)?
