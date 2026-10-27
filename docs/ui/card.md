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
| 1 | **Header** | Optional. Either a title (`h2`) with an optional description below it and actions, or the content navigation (‹ › period navigation, path bar) with the view switch at the far right. |
| 1a | **Secondary tabs** | Optional, below the header: secondary [Pill Tabs](tabs.md) of a section (Users › Settings: General, LDAP, Apps & API Access). |
| 2 | **Filter Bar** | Optional, below the header, when the page header has tabs and the card's content needs filters (Admin › People › Users). |
| 3 | **Body** | The content. |
| 4 | **Footer** | Optional: paging, counts, "Show more". |

## Titles

The page header names the page (`h1`), the card names its part (`h2`), sub-sections inside a card
are `h3`. All three are short, plain titles without `<strong>`.

- **Sizes:** page title (`h1`) `$font-size-lg`, card title (`h2`) a touch smaller
  (`$font-size-base * 1.125`), section (`h3`) `$font-size-base`; all semibold, in the highlight
  colour. A section heading has more space above than below.
- **Below tabs, the active tab names the card.** Settings › General → card "General",
  Users › Groups → card "Groups". With secondary tabs in the card, the card title is the section
  (Users › Settings → card "Settings" with General / LDAP / … below it).
- **Without tabs, the card has no title** when it would only repeat the page title (Modules,
  Account › Modules, Search). A short explanation is then the first, muted line of the body.
- **Sub-pages** (add, edit, delete) take their action as title: "Edit user: Jane Doe", with a
  back arrow before it leading to the overview (labelled "Back to overview").
- **Description:** only when it adds something the title doesn't say (e.g. "These settings can be
  overwritten by users"), never "Here you can configure …". Ordinary text below the title, at
  most two sentences, never as a separate block in the body.
- **Sections** inside a card: a plain `<h3>`, styled by the card (`h4` is not used for this). A
  short explanation below it is ordinary text as well; muted text is for hints under form fields.
  A settings form with several groups puts them into [accordions](accordion.md)
  (`ActiveForm::beginCollapsibleFields()`) instead; a section that would only repeat the card's
  title or tab gets no heading.

## Header actions

The card header uses the same actions and order as the
[page header](page-header.md#actions): tools, at most one primary action, then ⋮. The difference
is the look of the tools: **ghost** in a card header, **secondary** in the page header.

| | Page header | Card header |
|---|---|---|
| Is a card of its own | yes | no, it is the top of a card |
| Heading | `h1`, exactly one per page | `h2`, several per page possible |
| Pill Tabs | yes | no |
| Back arrow | yes | yes, on a sub-page |
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
        <div class="card-heading">
            <h2 id="w3-title" class="card-title">Settings</h2>
            <p class="card-subtitle">How people sign up and what they may do.</p>
        </div>
        <div class="card-header-actions">…</div>
    </div>
    <div class="card-tabs"><nav class="nav-scroller" aria-label="Settings"><ul class="nav nav-pills nav-pills-secondary">…</ul></nav></div>
    <div class="card-body">…</div>
    <div class="card-footer">…</div>
</section>
```

| Class | Element |
|---|---|
| `.card`, `.card-header`, `.card-title`, `.card-body`, `.card-footer` | Bootstrap |
| `.card-subtitle` | Bootstrap; the description below the title |
| `.content-card` | The content card's look, set as the card's `--bs-card-*` variables |
| `.card-heading` | Title and description, taking the header's free width |
| `.card-header-actions` | Header buttons, the actions and the ⋮ menu, right-aligned |
| `.card-tabs` | The secondary tabs between header and body |

- A card without title is a `<div>`; without title, header content and actions it has no header.
- **Look:** `.content-card` sets the card's `--bs-card-*` variables: no border,
  `$content-card-border-radius` (`$border-radius-lg`), the shadow of the page header
  (`$content-card-box-shadow`, `$content-card-box-shadow-dark`), `$content-card-bg`
  (`--hh-background-color-main`), a transparent header. Padding stays Bootstrap's.
- **Why a modifier, not Bootstrap's `$card-*`:** several modules (survey, ecommerce, matchmaking,
  classified-space, …), themes and the old card grid (`.container-cards .card`) use a plain
  `.card` and expect Bootstrap's look. Changing `$card-*` globally would restyle all of them.
- **Older views inside a card:** a `.panel-body` directly in the card body (module views rendered
  into a layout that is now a card) adds no padding of its own; two of them are spaced by `$spacer`.
- **File:** `_card.scss`, behind `$prev-card`, with `$content-card-header-gap`,
  `$content-card-title-font-size`, `$content-card-title-font-weight`,
  `$content-card-header-padding-top` / `-bottom` (1rem above the title as in the page header,
  1.5rem from title to content), `$content-card-tabs-padding-top`,
  `$content-card-subtitle-margin-top`, `$content-card-section-title-font-size`, `-font-weight`,
  `-margin-top`, `-margin-bottom` and
  `$content-card-margin-bottom` (the last card's distance to the footer, as a panel's).

## Implementation in core

**In core (1.20):**

- **PHP:** `humhub\widgets\ContentCard` (`begin()` / `end()` around the content; `title`,
  `titleTag` (default `h2`), `description` (HTML), `badges` (HTML next to the title, e.g.
  "Administrative group"), `backUrl` and `backLabel` (the back arrow of
  a sub-page, e.g. "Back to overview"), `buttons` (HTML, e.g. a secondary text button for an
  action that is not a save), `actions`, `menu`, `tabs` (secondary `PillTabs`), `footer`,
  `options`). The arrow is the page header's (`widgets/views/_backLink.php`); a heading inside a
  card body renders that partial before it. `Button::back()` (a text button) stays for modules.
- **Layouts:** a layout that used to wrap its views in a `.panel` (the administration's
  sections, the account) leaves that out when the view rendered a card:
  `ContentCard::isRendered($view)`. A view whose card comes from a Vue island calls
  `ContentCard::markRendered($view)`. The account layouts, `_authenticationLayout`,
  `_advancedLayout` and the group's `_manageLayout` render the card themselves, so the views
  inside them only bring the content; the Information layout renders one when the view does not.
- **Where:** the administration (Users, Spaces, Modules, Settings, Information, LDAP, Apps & API
  Access, E-Mail Summaries, Notifications), the account section, search and the licence page.
  Space and profile pages, the stream and module pages such as the wiki are not converted.
- **Vue:** `ContentCard.vue` (`<content-card>`): props `title`, `titleTag`, `description`, `actions`, `menu`;
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
| `.panel.panel-default` with `.panel-heading` and `.panel-body`, written by hand in views, styled by `_bootstrap3.scss` | `ContentCard`. Done for the administration and the account; module pages, the stream, space and profile pages follow. The section layouts still wrap a view without card in a `.panel`. |
| `h4` page titles inside panels (larger than the page title) | The card title; sections `h3` |
| `humhub\widgets\PanelMenu`: a ⋮ menu with a collapse option in panel headings | The ⋮ `DropdownMenu` of the card header |
| Headings with `<strong>` for the emphasised word ("**User** administration") | Plain titles |

## Open questions

1. **`PanelMenu`:** its collapse option (fold a panel) is not in the design. Keep it as an
   optional card feature, or drop it?
2. **Card without header:** the design has cards that are only content (the Tasks board). Is a
   header-less card the default, or should every card have a title for accessibility (visually
   hidden if needed)?
