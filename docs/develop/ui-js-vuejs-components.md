# Vue.js Components

> Part of the [Vue.js integration](ui-js-vuejs.md) documentation. This chapter covers authoring and using components: where a module's Vue sources live, the components core ships, the registry, mounting islands into server-rendered pages, the `VueComponent` PHP widget, and the composables bridging into existing platform services. For motivation, goals, constraints and the overall architecture, see the [overview](ui-js-vuejs.md).

## Module file layout

```
protected/humhub/modules/like/
├── assets/
│   └── LikeVueAsset.php              # VueAssetBundle; its namespace names the module → js/humhub.like.vue.js
├── vue/                              # sources — plain source code like views/, never published
│   └── LikeButton.vue                # auto-registered under its filename
└── resources/
    └── js/
        ├── humhub.like.vue.js        # committed build artifact
        └── humhub.like.vue.js.map
```

Every top-level `.vue` file directly inside `vue/` is registered under its filename — `LikeButton.vue` becomes `register('LikeButton', ...)`, no code required. The filename is therefore a component name in the same sense as a registered name below: PascalCase with a dashed tag form, enforced at build time (a violating filename fails the build with a message naming the offending file). Files inside subdirectories of `vue/` are internal building blocks — imported by the public, top-level components but never registered themselves.

An optional `vue/index.js` replaces the generated entry when a module needs custom registration logic; when present it is used verbatim and the filename convention above does not apply. That is where explicit `register()` calls against `@humhub/vue` go:

```js
import { register } from '@humhub/vue';
import LikeButton from './LikeButton.vue';

register('LikeButton', LikeButton);
```

The `@humhub/vue` import is developer-experience sugar only: the build marks it external and maps it onto the `humhub.modules.vue` global — nothing gets bundled twice.

External modules use the identical layout relative to their module root.

## Core component set

Core ships its own components at `protected/humhub/vue/`, built via `grunt build-vue --module core` into `protected/humhub/resources/js/humhub.core.vue.js` (`CoreVueAsset`) — infrastructure shared platform-wide, not tied to any single module, and always available wherever any other module's island might nest them (`CoreVueAsset` is listed in `CoreBundleAsset::STATIC_DEPENDS`).

- **`RichTextOutput`** — renders a rich text message (the processed markdown `RichText::outputMarkdown()` returns) inside an island: loads the message's oembed previews, then boots the richtext display widget on the `RichText::output()` envelope via `v-additions`. See [Legacy interop: RichTextOutput](ui-js-vuejs-interop.md#richtextoutput) for the envelope, the oembed and the XSS-safety contract.
- **`LegacyFormWrapper`** — hosts a server-rendered legacy widget shell (the rich text editor) inside an island and exposes a small, typed API to the rest of the Vue tree. See [Legacy interop: LegacyFormWrapper](ui-js-vuejs-interop.md#legacyformwrapper) for the `__VUEFORM__` token contract and the widget-instance APIs it exposes.
- **`DropdownMenu`** — a generic dropdown-toggle menu, the Vue analog of the `nav nav-pills preferences` / `.dropdown-toggle` + `.dropdown-menu` markup pattern PHP widgets render throughout the app (e.g. `humhub\widgets\PanelMenu`, `content\widgets\WallEntryControls`). Any island's template can reach for `<DropdownMenu>` instead of hand-rolling this structure again; its default slot holds free-form menu items, and an optional `menuId`/`entries` mode renders a data-driven, `registerMenuEntry()`/`removeMenuEntry()`-extensible item list after the slot — see [Extending islands: menu entries](ui-js-vuejs-extensions.md#menu-entries). Toggling, closing (click-away/Escape) and keyboard navigation are handled entirely by Bootstrap's own dropdown JS via `data-bs-toggle="dropdown"` — nothing here is Vue-owned, so Vue-rendered markup behaves identically to server-rendered markup. A public `open(event)` method lets the host raise the menu from somewhere other than its own toggle; handed a mouse event it opens **at the pointer**, which is what gives a list of Vue-rendered rows the right-click context menus the platform's legacy `$.fn.contextMenu` gave server-rendered ones.
- **`ExtensionSlot`** — the Vue analog of PHP widget stacks. See [Extending islands: extension slots](ui-js-vuejs-extensions.md#extension-slots) for the full contract.
- **The `HumHubForm` suite** (`HumHubForm`, `TextField`, `TextareaField`, `CheckboxField`, `SelectField`, `UploadField`, `SubmitButton`, `RichTextField`) — a native form layer with `yii\bootstrap5\ActiveField` markup/naming parity and a shared server-side-422 error contract, plus `UploadField` for file uploads and `RichTextField` embedding `LegacyFormWrapper` as a legacy-citizen field for the rich text editor. See [Form suite](ui-js-vuejs-forms.md) for the full component/props reference and a worked example — the comment section's own form (`CommentForm.vue`) is the reference consumer.
- **`UiModal`** (`<ui-modal>`, `protected/humhub/vue/UiModal.vue`) — the first native, Vue-owned modal: reuses the exact Bootstrap 5 markup/CSS classes the legacy `humhub.ui.modal.js` bridge renders (`.modal.fade`, `.modal-dialog`, `.modal-content`, `.modal-header`/`.modal-title`/`.btn-close`, `.modal-body`, `.modal-footer`, `.modal-backdrop`) but owns open/close/backdrop/keyboard/focus/scroll-lock itself instead of wrapping `bootstrap.Modal`.

  | Prop | Type / default | |
  |---|---|---|
  | `show` | `Boolean`, `false` | `v-model:show` — fully controlled visibility, the component never closes itself |
  | `title` | `String`, `null` | rendered by the fallback header (ignored when the `header` slot is used) |
  | `size` | `'small'` \| `'normal'` \| `'large'`, `'normal'` | → `modal-sm` / no class / `modal-lg` |
  | `dialogClass` | `String` \| `Array` \| `Object`, `null` | extra classes on `.modal-dialog` — a dialog's own width or styling scope (the marketplace's `c-mp-dialog`) |
  | `backdropClose` | `Boolean`, `true` | clicking the dimmed area outside the dialog requests a close |
  | `keyboard` | `Boolean`, `true` | Escape requests a close (listener only attached while open) |

  | Slot | | Event | |
  |---|---|---|---|
  | default | the modal body | `update:show` | close requests (backdrop/Escape/close button) |
  | `header` | replaces the fallback title + close button; receives `{ titleId }` so a custom heading can wire itself to `aria-labelledby` | `opened` | fires once the dialog finishes mounting and is focused |
  | `footer` | omitted entirely (no `.modal-footer` element) when not provided | `closed` | fires once focus has been restored to whatever had it before opening |

  ```html
  <UiModal v-model:show="showUserList" title="Users who like this">
      <UserList :url="userListUrl" />
  </UiModal>
  ```

  A modal unmounted while open (its host island goes away, or a `v-if` removes it instead of flipping `show`) cleans up like a close: scroll lock and Escape listener are released and focus returns to what had it before opening — so a dialog component may be rendered with `v-if` only while it is needed, as the marketplace's dialogs are. `LikeButton.vue`'s user-list modal (below) is the reference consumer. Stacking two modals (a second `UiModal`, or a legacy `#globalModal`) open at the same time is out of scope — see the component's own docblock.

- **`StatusBar`** (`<status-bar>`, `protected/humhub/vue/StatusBar.vue`) — the platform's user-feedback bar, and the first **infrastructure island**: nothing renders it with props and no other component nests it. `humhub\widgets\StatusBar` (a `LayoutAddons` widget, so it exists on every full page) mounts it once as `<status-bar id="status-bar">`, and every message reaches it through the bridge instead of through props — see [Legacy interop: status messages](ui-js-vuejs-interop.md#status-messages) for the queue that makes a message triggered before the island mounts work. It behaves like the jQuery bar it succeeds — one message at a time, the same level classes, the same auto-close timings, the same details toggle for error traces — and looks like the platform's toast: `.status-bar-body` is the fixed layer along the bottom, `.status-bar-content` the translucent card in its centre with border and outline in the level's colour (`status-bar-info|success|warning|error`), icon, message and controls in a `.status-bar-header` row. The 220 ms rise is a CSS transition in `_user-feedback.scss` — the component only toggles `status-bar-visible`.
- **Page toolbar and card directory kit** — `PageToolbar`, `FilterBar`, `FilterSelect`, `CardDirectory`, `CardGrid`, `CardSkeleton`: the upper box of a page (title, actions, filter bar) and the card directory built on it (the marketplace and the spaces directory today, people to follow), styled after the HumHub design system v2 (`resources/scss/_page-toolbar.scss`, `resources/scss/_card-directory.scss`). `protected/humhub/modules/marketplace/vue/MarketplaceBrowser.vue` is the reference consumer; `protected/humhub/modules/space/vue/SpaceDirectory.vue` shows the per-viewer states (`itemStates`), its own card and skeleton and toolbar actions from a PHP menu.
  - **`PageToolbar`** (`<page-toolbar>`) — pure layout, loads nothing: the `c-page-toolbar` card, at most 1440px wide and centred, with a header row of the `title` prop (rendered as `titleTag`, default `h1`, and labelling the toolbar) and the actions right-aligned beside it, then the default slot — typically a `FilterBar`. Actions come as data through the `actions` prop — `[{ id, icon, label, url, modal?, variant?, htmlOptions? }]`, typically the entries of a PHP menu (`humhub\widgets\menu\Menu::getEntriesData()`, which takes the variant from an entry's `btn-<variant>` class and `modal` from `data-action-click="ui.modal.load"`) — rendered as icon links (`a.btn.btn-<variant>.c-icon-button` with the Tabler icon `ti ti-<icon>`, `aria-label` and `title` = `label`, `data-action-id` = `id`; `variant` is `secondary` (default), `accent` or `primary`; `modal: true` opens `url` in the global modal through the bridge's `modal.load(url)`, otherwise it is a plain link; `htmlOptions` are extra attributes, a `class` added to the button classes), followed by the `actions` slot for actions of the page's own (icon buttons: `.btn.c-icon-button`). The header is only rendered with a title or actions. Any module page can head its own view with it, not only a card directory.
  - **`FilterBar`** (`<filter-bar>`) — the filter bar for the filters of a `humhub\components\filter\FilterSet`, definitions handed over as the `filters` prop, of the types `text` (the search field), `select` (a **`FilterSelect`**: a single-select listbox whose placeholder is the "all" state, a clear button once a value is set, optionally loading further options from an `optionsUrl`; an option carrying `params` sends those request parameters instead of its value, so one select can combine parameters of the list — the spaces directory's "Archived" status sends `archived=1`), `tags` and `checkbox`; `filter-<key>` slots (`{ filter, value, update }`) replace one control. A "clear all" button slides in once a visible filter is set, and on narrow widths the bar collapses behind a filter toggle. Its `v-model` holds the **applied** values: it keeps what the controls show apart and emits `update:modelValue` only once a change applies — text filters debounced, all others at once — so the owner simply reloads on every emit. It starts from `modelValue` (missing keys at their defaults, so `{}` will do), overridden by the page URL, and emits those initial values while it is created. With `syncUrl` (default `true`) applied values are mirrored into the page URL (`history.replaceState`, defaults omitted, PJAX's own `history.state` kept current). A definition flagged `hidden` is context a link carries in (the marketplace's `id`), URL-synced and sent but never rendered, and dropped as soon as a visible filter changes. `idPrefix` prefixes the controls' ids. Instance API through a template ref: `setFilter(key, value)` (sets one filter from outside the bar, applied like a change in it but at once — a text filter too, without the debounce; `false` for an unknown key) and `reloadOptions()` (re-runs every `optionsUrl` filter's load).
  - **`CardDirectory`** (`<card-directory>`) — a card page: a `PageToolbar` (`title`, `titleTag`, the `actions` prop, the `actions` slot with `{ meta, total }`) holding a `FilterBar` (`filters`, `syncUrl`, `idPrefix`, the `filter-<key>` slots), an optional `notice` slot (`{ meta, total }`) between toolbar and grid, and a **`CardGrid`** (a CSS grid of cells at least 264px wide, cards popping in staggered, **`CardSkeleton`** placeholders while the first page loads — replaceable per cell by the `skeleton` slot, `{ index }`), fed page by page from the `url` endpoint answering the offset-page envelope (the `url` may carry a query string of its own, e.g. `/api/v2/space?purpose=directory`; the filter and page parameters are appended). Every applied filter change starts at page 1, a response that is no longer the latest request's is dropped, and further pages load when the grid's end scrolls into view (a "Show more" button remains for keyboard users). A failed first page replaces the grid with an error, a failed later page sits below the cards; both retry exactly the page that failed. Envelope fields beyond the standard ones are kept when named in `metaKeys` (the marketplace's `updateCount`). After every loaded page it emits `loaded` (`{ total, meta, values }` — `values` being the filter values the page was loaded with, which the marketplace uses to mark the search in its cards). Per-viewer states of the cards (a membership, a friendship) come through the optional `itemStates` prop, `(ids) => Promise<{ [id]: state }>`: called once per loaded page with the keys (`itemKey`) of that page's items, after the page is rendered (never for an empty page); an answer for a list no longer shown (replaced by a later page 1 — `reload()` and filter changes refetch the states with the list) is ignored, a failure is logged. The owning island passes its card through the `card` slot (`{ item, index, state }` — `index` within the loaded page, for the stagger; `state` the item's state, `undefined` while its page's states load, `null` when missing from the answer or when the request failed) and an `empty` slot, and reaches `reload()`/`loadMore()`/`retry()`/`replaceItem(id, item)`/`replaceState(id, patch)` (merges `patch` into one item's state, shallow; a patch made while the states are still loading survives their answer)/`setFilter(key, value)`/`reloadFilterOptions()` (both delegated to its `FilterBar`) through a template ref.

  A page with a view of its own below the toolbar — a file list, say — composes the first two itself and loads on every `v-model` update; with `filterValues` starting as `{}`, the first update (the defaults, or what the URL carries) arrives while the bar is created:

  ```html
  <PageToolbar title="Files">
      <template #actions>
          <button type="button" class="btn btn-secondary c-icon-button" :aria-label="labels.settings" @click="openSettings"><i class="ti ti-settings" aria-hidden="true"></i></button>
          <button type="button" class="btn btn-accent c-icon-button" :aria-label="labels.add" @click="add"><i class="ti ti-plus" aria-hidden="true"></i></button>
      </template>
      <FilterBar v-model="filterValues" :filters="filters" id-prefix="files-filter" @update:model-value="load" />
  </PageToolbar>
  <FileList :files="files" />
  ```

- **Item browser kit** — `ViewSwitch`, `PathBar`, `TileGrid`, `SelectionMenu`, `DropZone`, `ProgressFrame`: the parts of a view that browses a collection level by level (files and folders, albums, pages), after the HumHub design system v2's file browser (`resources/scss/_item-browser.scss`). None of them holds state of its own — selection, drop targets and the level shown come in as props, what the user wants goes out as events — so one owner keeps a tile view and a list view of the same items in step. `cfiles`' file browser is the reference consumer.
  - **`ViewSwitch`** (`<view-switch>`) — icon buttons switching between views (`v-model`, `options: [{ value, icon, label }]`), pressed state as `aria-pressed`; for a `PageToolbar`'s `actions` slot.
  - **`PathBar`** (`<path-bar>`) — the grey path bar: the root as an icon link (`rootLabel`, `rootUrl` — both required), ancestors as links, the current level as text, a back button at 768px and below and an `end` slot (e.g. a `SelectionMenu`). `navigate(id)` fires on a plain click on the root, an ancestor or the back button (a modified click or the middle button is left to the browser, so the `url`s open in a new tab). The root and every ancestor — never the current level — are drop targets: `canDrop(id, event)` decides, `dropTargetId` highlights one (`null` for the root, `undefined` for none), and `drag-over(id, event)`/`drag-leave(id, event)` (once, only when the drag actually leaves a crumb rather than moving onto one of its children)/`drop-on(id, event)` report it.
  - **`TileGrid`** (`<tile-grid>`) — tiles of at least 136px with the slots `thumb`, `name` (stretched over the whole tile, one click target), `meta`, `actions` and `empty`; items are keyed by `itemKey` (default `id`, unique across the item kinds) and named by `labelFor` (default `(item) => item.title ?? ''`) for messages built from them, e.g. the selection checkbox's accessible name ("Select {name}"). Render the thumbnail `<img>` and the name `<a>` with `draggable="false"`, so the tile itself — not either of them — is the drag source. Checkboxes (`selectable`, `selection`, `toggle-select(item, { range })`), right-click `context-menu(item, event)`, dragging (`draggable`, `canDrop`, `dropTargetKey`; `drag-start(item, event)`/`drag-end(item, event)` only for draggable tiles and paired with each other, the tile as the drag image; plus `drag-over(item, event)`/`drag-leave(item, event)`/`drop-on(item, event)`), uploading items (`uploading`, `progress`) framed by a `ProgressFrame`, skeleton tiles (`skeletonCount`, default 12) while `loading` (set `items: []` together with `loading: true` on a level change, or the new pane slides in with the old items — `aria-busy` on the root reflects it), a slide between levels (`level`, `direction`) and "Show more" paging like `CardGrid`. `dropTargetKey`'s `null` means no target, unlike `PathBar`'s `dropTargetId`, where `undefined` means no target and `null` is the root — an owner tracking drop targets in both keeps two separate values (or a small map), not one shared `null`.
  - **`SelectionMenu`** (`<selection-menu>`) — the actions for a selection: a ghost icon button labelled with the selection's size, opening a `DropdownMenu` of `entries`/`context`, shown only while `count` > 0. `menuId` (required) namespaces the entries in the `registerMenuEntry()` registry per owning view (e.g. `cfiles.selection`), since `context` differs per consumer.
  - **`DropZone`** (`<drop-zone>`) — files from the desktop: an overlay while they are dragged over its content, `drop(files, event)` on a drop, a refused state for `accept: false`. Nested drag targets are counted so moving between children doesn't flicker, a child that takes a drop itself (a folder tile taking an upload) stops it from reaching the zone, and a drag that leaves to an element outside the zone always resets it.
  - **`ProgressFrame`** (`<progress-frame>`) — a progress bar (`value`, 0–100) drawn as the outline of its container in the container's own pixels — the upload state of a `TileGrid` tile.

  File payloads carry the Tabler name of their file-type icon as `icon` (`humhub\libs\MimeHelper::getIconNameByExtension()`), so a tile renders `<i class="ti ti-<icon>">` without a mapping of its own.

## Module-provided shared components

Core's own component set above is one instance of a more general pattern: **any** module can expose a component for other modules to nest, the same way `LikeButton` (the `like` module) and `UserImage`/`UserList` (the `user` module) already do — nothing beyond the ordinary [module file layout](#module-file-layout) and [component registry](#component-registry) is required. The consuming module's own asset bundle just adds the providing module's `*VueAsset` to its `$depends`, so the provided component is guaranteed to be registered before the consumer's own bundle script runs (see [Loading and bundling](#loading-and-bundling)) — a plain dependency edge between two asset bundles, no different from any other cross-module PHP dependency.

- **`UserImage`** (`<user-image>`, `humhub\modules\user\vue\UserImage.vue`, `UserVueAsset`) — a user's profile image, the Vue analog of `user\widgets\Image`/`ui\widgets\BaseImage`: props modeled on the serialized author shape (`guid`, `displayName`, `url`, `imageUrl`, `contentContainerId`) plus display options (`online`, `size`, `link`, `imageAlt`). The accessible name of the image is built client-side from the same `base` message `Image::run()` uses (the API ships no localized text), and `online` is a display option a caller passes — the user shape carries no presence, so the indicator only appears where a consumer resolves it. Reproduces `Image::run()`'s `rounded` sizing, `data-contentcontainer-id` popover hook and online-status overlay (`has-online-status` + the `img-size-small`/`medium`/`large` bucket classes) so existing theme CSS applies unchanged — see its own docblock for the one deliberate deviation (no extra wrapping `<span>`). The comment island's `CommentVueAsset` depends on `UserVueAsset` for exactly this reason: `CommentEntry.vue` nests `<UserImage v-bind="comment.author" />` without importing it.
- **`SpaceImage`** (`<space-image>`, `humhub\modules\space\vue\SpaceImage.vue`, `SpaceVueAsset`) — a space's profile image, the Vue analog of `space\widgets\Image`: props modeled on the serialized space shape (`id`, `name`, `url`, `color`, `imageUrl`, `contentContainerId`) plus display options (`width`, `height`, `link`, `acronymCount`). Renders either the image or the coloured acronym tile, keeping the widget's own class contract (`space-profile-acronym-<id>`/`space-profile-image-<id>`, `d-none-space-image`, the border-radius buckets) so theme CSS and the space-image swap keep working. The acronym is derived client-side exactly like `Image::getAcronym()` — nothing localized about it. The notification island is the reference consumer (a notification's space badge).
- **`FollowButton`** (`<follow-button>`, `humhub\modules\space\vue\FollowButton.vue`, `SpaceVueAsset`) — the caller's follow of a space on `GET|PUT|DELETE /api/v2/space/<id>/follow`: "Follow" ↔ "Following" (reading "Unfollow" on hover/focus, confirmed before the `DELETE`), nothing for a member or where following is not possible. Props: `spaceId`, `spaceName`, `initial` (the endpoint's `{isFollowing, followerCount, canFollow}`, so a page that has the state already — `space/states` — needs no request), `isMember`, the state classes `followClass`/`followingClass`. Mounted by `space\widgets\FollowButton` in the space header and nested by the space directory's `SpaceCard`. Every instance of the same space, and its `MembershipButton`, stay in sync through the `events` bridge: `space:follow-changed` `{spaceId, isFollowing, followerCount, canFollow}` after a follow change, `space:membership-changed` `{spaceId, state}` from the `MembershipButton` (joining hides the button, leaving offers following again); it also emits `change` with the follow payload.
- **`AttachedFiles`** (`<attached-files>`, `humhub\modules\file\vue\AttachedFiles.vue`, `FileVueAsset`) — the files attached to a record: the media grid (audio / video / image previews) followed by the list of all attachments. The Vue analog of `file\widgets\ShowFiles`, which is now nothing but its server-side mount point, and at the same time the attachment renderer of any island holding serialized files — `CommentEntry.vue` is the first, and the two used to be separate implementations of the same visual that had already drifted apart. Props: `files` (the API's file shape, see [HTTP API framework](concept-api.md)), `galleryId`, plus the display options `preview`, `excludeMedia` and `fluid`. Keeps the `.post-files` / `.col-media` grid structure of the view it replaces and the `.well .post-file-list > ul.files > li.file-preview-item` structure the `file.Preview` JsWidget rendered, so theme CSS applies unchanged; both roots run `v-additions`, so the gallery/lightbox and popover additions pick the rendered markup up whether the component is an island of its own or nested in one. A server-side caller may refine each file with two **presentation hints** the HTTP API does not carry — `viewUrl` (open the file in the global modal, i.e. a module contributed a viewer for it) and `highlight` (a search hit) — because both are caller specific; `ShowFiles` resolves them, the cacheable comment payload deliberately does not. See its own docblock for the deviations from the markup it replaces, `docs/develop/module-migrate.md` for the user-visible ones.
- **`UserList`** (`<user-list>`, `humhub\modules\user\vue\UserList.vue`, `UserVueAsset`) — the Vue analog of `user\widgets\UserListBox`: loads and renders a page of users (avatar + linked display name, `.hh-list` row styling) from any endpoint, then a "Show more" link for the next page. **Generic by design** — it is not tied to the like module: point `:url` at any endpoint returning either the API's list envelope (`{results, total, page, pageSize, pages}`, rows being user shapes — see [HTTP API framework](concept-api.md)) or the legacy `{ total, users, hasMore, nextPage }` shape. Props: `url` (required), `pageSize` (optional — sent as the `limit` query param). Emits `user-click` (the clicked user) on every row click, without ever `preventDefault()`-ing the row's own `<a href>` navigation — the Vue equivalent of legacy `UserListBox` rows carrying `data-modal-close="1"`; a consuming island wires this to close its own modal (`LikeButton.vue` does). The like module's user-list modal (`LikeButton.vue`, feeding it `GET /api/v2/like/<recordId>/users`) is the reference consumer — see its own docblock for the deliberate deviations from `UserListBox`'s legacy view (no display-name subtitle line; "Show more" appends in place instead of replacing the whole modal per page). One further **accepted deviation**: `userListBox.php`'s legacy `Modal::beginDialog(['bodyOptions' => [...]])` gives its body a negative-margin, full-width treatment (`margin: 0 calc(var(--hh-modal-content-padding) * -1)`) that `UiModal`'s plain `.modal-body` does not reproduce — `UserList`'s rows render with `UiModal`'s normal body padding on all sides instead of bleeding to the dialog's edges.

## Loading and bundling

- **Vue runtime:** Vue 3, runtime-only build, added like all frontend libraries via Composer/asset-packagist (`npm-asset/vue`). A `VueAsset` bundle serves `vue.runtime.global.js` in debug mode (warnings, Vue Devtools support) and `vue.runtime.global.prod.js` otherwise. It becomes part of the core bundle, so every module asset bundle (which implicitly depends on `CoreBundleAsset`) is guaranteed to load after it.
- **`humhub.vue` core module:** a new `protected/humhub/resources/js/humhub/humhub.vue.js`, part of the core JS list like `humhub.i18n.js`. Exposes the registry, the mounter and the composables under `humhub.modules.vue`.
- **Module components:** each module ships one committed artifact (`resources/js/humhub.<module>.vue.js`) through a `humhub\components\assets\VueAssetBundle` subclass that declares nothing but lists the `*VueAsset` bundles of the modules whose components it nests by tag. The dependency on `CoreVueAsset` — and through it on `CoreApiAsset` and the runtime — is implied, and it is load-bearing: Yii emits bundles in registration order, and a widget in a view registers its bundle before the layout registers the core bundle, so without the edge the module script would run first. Yii's per-page asset registration is the lazy-loading mechanism — a module's Vue code only loads on pages that render one of its components. Nothing loads globally except the runtime, `humhub.vue` and the core component set.
- **Later stage (optional):** on-demand loading via dynamic `import()` at first mount for heavy components. Not implemented: `register()` takes a component definition, and loading relies on asset bundles only.

## Component registry

At the core, every component is registered by name through a small API:

```js
import { register } from '@humhub/vue';
import LikeButton from './LikeButton.vue';

register('LikeButton', LikeButton);
```

Modules normally never write this code themselves — the filename convention (see [Module file layout](#module-file-layout)) generates it for every top-level component. The explicit `register()` call remains the underlying API for two cases: a `vue/index.js` with custom registration logic, and registering components at runtime (e.g. `registerSlotComponent`/`registerMenuEntry`, see [Extending islands: extension slots](ui-js-vuejs-extensions.md#extension-slots) and [menu entries](ui-js-vuejs-extensions.md#menu-entries)).

- **Names are platform-wide unique**, PascalCase with a dashed tag form (`LikeButton` → `<like-button>`, `HButton` → `<h-button>`, `PDFViewer` → `<pdf-viewer>`) — analogous to PHP class names sharing one autoloader. Registering the same name twice is a debug-level no-op (artifact scripts legitimately re-execute with every ajax response that includes them); two different names deriving the same tag is an error, and the first registration wins.
- Every registered component is made available **globally in every island app**. That is what enables cross-module nesting: the comment section's template uses `<LikeButton :content-id="..."/>` without importing or even knowing the like module.
- A tag for an **unregistered** component (module disabled, artifact not loaded) renders as an inert placeholder instead of an error — modules stay optional. In debug mode a console warning identifies the missing component.
- **Late registration is safe:** when a component registers after the page initialized (script injected with a PJAX/AJAX response), the registry immediately mounts any placeholder tags already waiting in the DOM. "HTML first, script afterwards" ordering is therefore uncritical. Late-registered components also become *resolvable* inside already-mounted islands — but an island that has already rendered a missing child only picks it up on its next reactive re-render.

## Mounting and lifecycle

Mounting is implemented as a [UI addition](ui-js-uiadditions.md). Since `ui.additions.applyTo()` already runs after the initial page load, after **every PJAX navigation**, and over every injected fragment (modals, stream entries, widget reloads, comment inserts), Vue islands inherit all of these code paths without any special handling. **PJAX is thereby solved by construction, not worked around.**

The mounter matches two selectors:

1. **Component tags** — the kebab-case form of every registered component name:

   ```html
   <like-button content-id="42"></like-button>
   ```

   Note the HTML parser lowercases tag and attribute names in server-rendered markup — `<LikeButton contentId="42">` only works *inside* Vue templates (which Vue compiles itself); in PHP views the kebab-case form is required.

2. **`[data-vue-component]`** as an explicit fallback for dynamic cases:

   ```html
   <div data-vue-component="LikeButton" data-props='{"contentId":42}'></div>
   ```

Each match becomes its own `createApp()` instance sharing a common plugin set (registry components, i18n, config, error handler).

**Props**

- Simple scalars as individual attributes, kebab-case in HTML, mapped to camelCase and type-coerced (`Number`, `Boolean`) using the component's prop declarations.
- Complex data as a single JSON attribute (`props` on component tags, `data-props` on the fallback form); individual attributes override JSON keys.
- **Initial state travels with the HTML.** A like button receives its current count and liked-state as props — no extra request on page load, no flash of empty content.
- Any markup inside the tag acts as a loading placeholder and is replaced on mount.

**Unmounting** is owned by a `MutationObserver`: any app whose root node leaves the DOM is unmounted — the PJAX navigation swapping `#layout-content`'s content wholesale, a closed modal, a deleted stream entry. The runtime deliberately does **not** hook the module-lifecycle `unload()` signal: that fires when a navigation *starts* (before the DOM swap — which a canceled unsaved-changes confirm, or an aborted request, may prevent entirely), and eagerly unmounting on it killed every island on the still-visible page.

No leaked apps, no zombie state, no work for component authors.

**i18n preloading:** a component may declare required message categories (`i18nCategories: ['LikeModule.base']`); the mounter preloads them through `humhub.i18n` before mounting, mirroring `requiredI18nCategories` of classic modules. **Only the TOP-LEVEL component actually being mounted as an island is ever consulted** — a component nested inside another one's template (e.g. `UserList` inside `LikeButton`'s modal) has its own `i18nCategories` read only if and when *it itself* is later mounted directly as an island; while nested, that declaration is inert. A top-level island must therefore declare every category its whole subtree needs, not just its own — `LikeButton.vue` declares `LikeModule.base` for itself plus `UserModule.base`/`base` for the nested `UserList`/`UiModal` it renders, the same way `CommentSection.vue` declares `UserModule.base` and `base` for the nested `UserImage` (its online-status label and alt phrase), alongside its own `CommentModule.base`/`ContentModule.base`.

### Initial data: embed or load

Two ways for an island to get its data, and the choice depends on the island, not on taste:

- **Embed** it as props when a page carries many small islands whose data is local and cheap — like buttons, comment sections. A warm payload cache makes the first paint request-free, and dozens of islands do not turn into dozens of requests.
- **Load** it after mounting, with skeleton placeholders, when the island is the page — directories (marketplace, people, spaces) and module views such as a task list or a calendar — and its data is expensive, remote or large. The page then stands at once, and nothing slow sits in the server render. `CardDirectory` always loads; a `VueWidget` of such a page renders only cheap, local props plus a placeholder of the same skeleton (see `humhub\modules\marketplace\widgets\MarketplaceBrowser`, which never contacts humhub.com while rendering).

## Using components from PHP

Two equivalent ways:

```php
<?php /* a) plain tag — the module's Vue asset bundle must be registered on the page */ ?>
<like-button content-id="<?= $content->id ?>"></like-button>

<?php /* b) widget helper — registers the asset bundle automatically, encodes props */ ?>
<?= VueComponent::widget([
    'name' => 'LikeButton',
    'props' => ['contentId' => $content->id],
]) ?>
```

`VueComponent::widget()` renders the tag form, JSON-encodes non-scalar props, and registers the asset bundle passed via `assetBundle`. No inline `<script>` per instance is ever emitted — props live in attributes, which keeps CSP nonces and PJAX re-execution out of the picture.

**Reserved prop names:** the client-side registry never reads the attributes `class`, `id`, `style`, `props` or anything starting with `data-` as props — `VueComponent` therefore throws when a prop maps onto one of them, or when a prop collides with an entry in `options`. Prop keys must be static, developer-controlled strings.

**The mount point is an element of the page, not a hook to hang one on.** Give it the id and classes the server-rendered element had (`options`), so theme CSS, the product tour and tests keep addressing it — `notification\widgets\Overview` mounts `#notification_widget.btn-group`, `activity\widgets\ActivityBox` mounts `#panel-activities.panel`. Two consequences worth knowing: a custom element is `display: inline` by default, so a block-level island needs a class that says otherwise (an inline box paints its background only behind its line boxes — a panel would show the page through its own contents); and the element is EMPTY until the island mounts, so anything measuring it — a test asserting the panel is there, the tour positioning itself — sees nothing. Pass `content` for a placeholder that gives it substance in the meantime.

**Migration mechanism:** existing PHP widgets keep their public API and simply render an island internally, by extending `humhub\widgets\VueWidget`: the subclass declares the component and its bundle, hands over what only the server knows via `getProps()`, gives the mount element its id and classes via `getOptions()` and a placeholder via `getPlaceholder()`; a `beforeRun()` returning `false` renders nothing, as with any widget. `LikeLink::widget(['object' => $post])` continues to work in every theme and module — there is no PHP view in between anymore (the earlier `views/likeLink.php` was removed), the widget IS the island's server side. Callers never notice the switch; `LikeLink`, `Comments`, `ShowFiles`, `MembershipButton`, `FollowButton` (space), `FriendshipButton`, `ActivityBox`, the notification `Overview` and the `StatusBar` are built this way.

**Guest states.** Islands that need to know whether the current visitor is logged in do not receive a `guest` prop from the server — they read it client-side from the `user` `registerJsConfig` section (`isGuest`, `loginUrl`, both populated by `CoreJsConfig` from `Yii::$app->user->isGuest` / `Yii::$app->user->loginUrl`) via `getConfig('user')` from `@humhub/vue`. `LikeButton` is the reference example: guests get the like **count**, non-interactively, plus a link that opens the login modal (`data-bs-target="#globalModal"`, same delegated handler as the user-list link) instead of the like/unlike controls.

## Bridge layer: composables

Vue components reach platform services through the `@humhub/vue` bridge, whose exports delegate to the existing infrastructure — nothing is reimplemented:

```js
import { apiUrl, client, events, getConfig, i18n, log, modal, oembed, pageTitle, url } from '@humhub/vue';
```

| Export | Delegates to |
|---|---|
| `i18n.t(category, message, params)`, `i18n.preload()` | `humhub.i18n` — ICU MessageFormat, localStorage cache; the message extractor parses `.vue` files |
| `client.get()`/`post()`/`patch()`/`put()`/`del()` | `humhub.client` — CSRF header, status handling, redirects; `patch`/`put`/`del` go through its `ajax()` |
| `apiUrl(path, params)`, `url(route, params)` | the HTTP API base (`/api/v2/`, from `CoreJsConfig`) and the platform's URL template (see below) |
| `modal.confirm()`/`modal.load()` | the existing global modal system — still the bridge for LEGACY flows (e.g. comment delete); Vue-native UI reaches for `<UiModal>` (see [Core component set](#core-component-set)) instead |
| `getConfig('user')` | values passed via `registerJsConfig` |
| `events.on()`/`off()`/`trigger()` | the global `humhub.event` bus — communication between islands and with legacy JS; a component unsubscribes its own handler in `unmounted()` |
| `log.error()`/`warn()`/`info()`/`debug()`, `status()` | `humhub.log` and the status bar |
| `oembed.load(urls, options)` | `humhub.oembed.js` — the previews `RichTextOutput` loads before rendering |
| `pageTitle()` | `ui.view` — the platform's per-page title state |

The older `use*()` composable names of the concept draft were never introduced; the plain exports above are the API.

Errors thrown in components hit a global Vue `errorHandler` wired to `humhub.log` and the existing status bar — one consistent error UX.

`url(route, params)` builds URLs for default-routed module endpoints (e.g. `url('/comment/comment/show', { id: 7, mode: 'popup' })`) against a template registered once, platform-wide, via `CoreJsConfig` (`Url::to(['/__route__'])`, passed through `registerJsConfig` under `url.template`) — pretty URLs yield `<baseUrl>/__route__`, otherwise `<baseUrl>/index.php?r=__route__`; the client fills in the route and appends `params` as a query string. This covers the common case of an island calling back into its own module's controller actions without a server round trip per link. It is **not** a router — there are no client-side route definitions, no navigation, no history handling; anything beyond filling in this one template (custom routes, non-default URL rules) still has its URL generated server-side and passed as a prop or config value, as before.

`url` in `@humhub/vue` is a thin delegate to the standalone core JS module `humhub.url`, so legacy (non-Vue) code can build the same URLs via `require('url').to(route, params)` from any `humhub.module()` — the Vue bridge does not implement its own URL logic anymore.

**Message extraction convention.** `php yii message/extract-module` parses `.vue` files, but only the full call form `i18n.t('Category', 'Message')` (typically in computed properties or methods) is recognized — category-bound helpers hide the category from the extractor. Templates should reference those computed labels instead of calling `t()` inline.

**Preloading vs. server labels.** Declared `i18nCategories` are loaded *before* mount — on a cold cache (and always in debug mode, since the localStorage translation cache is bypassed there, so the preload XHR fires on every page load) that delays the island behind a translation request. The LikeButton pilot uses this path: it declares `i18nCategories: ['LikeModule.base']` and calls `i18n.t('LikeModule.base', 'Like')` from a computed property, with no labels passed from PHP at all. It also demonstrates the complementary optimization for *state* rather than labels: `likeCount`/`currentUserLiked` are optional props — when the server already computed them (the normal case, e.g. on a stream page) no request happens, and the component only calls `GET like/<recordId>` when they are omitted. When both are omitted, the two round trips are **not** parallel: the i18n preload gates the mount itself (see "Mounting and lifecycle" above), and only once mounted does `created()` run and kick off the state fetch — so a fully stateless, cold-cache mount pays for the translation request and *then* the info request, serially. Passing initial state as props (as the PHP widget normally does) avoids the second request entirely; passing server-rendered labels too would avoid the first. For components where the translation round trip itself is FOUC-critical, server-rendered labels passed as props remain the documented alternative; reserve client-side i18n preloading for components with many or dynamic messages, or where avoiding it isn't worth the extra props.
