# Vue.js Integration — Roadmap

Open work of the Vue islands initiative in core. What is implemented is described in the
[chapter docs](ui-js-vuejs.md) and the [HTTP API framework](concept-api.md); this page lists
only what is still to do, and is intentionally brief.

## API & performance

- **Query batching** for the rest of the comment payload (`childCount`, files) on a cache
  miss — would take a 19-comment window from 77 SELECTs to roughly ten.
- **HTTP caching** on top of the server-side payload cache (ETag/Last-Modified, shared caches
  for guest-visible content) — core has no infrastructure for it yet.
- **Rate limiting** for the session-reachable endpoints: always-available API endpoints
  multiply request volume from every logged-in browser. Decide before the API is declared
  stable.
- **Portable message formats**: a `messageFormat` parameter (`richtext`, `markdown`, `html`,
  `plaintext`) for rich text fields, backed by `RichText::convert()`, the format part of the
  payload cache key; writes keep accepting richtext only.
- **Oembed endpoint**: `GET /api/v2/oembed` as the API counterpart of `oembed/index`, which
  `RichTextOutput` loads previews from today.

## One way per transition

Web routes an endpoint duplicates, still in place because other consumers depend on them:

- **File upload** — `file/file/upload` (`UploadAction`) next to `POST /api/v2/file`: the
  jQuery upload widget, the mobile app (`fileUploadUrl`) and module actions extending
  `UploadAction` still use it.
- **Space picker search** — `space/browse/search-json` next to `GET /api/v2/space`: the
  picker widgets expect its result shape. Moves together with the pickers becoming islands,
  which then search with `GET /api/v2/space?purpose=picker` (`ids`/`exclude` for the chosen
  spaces).
- **Module enabling** — `admin/module/enable|disable` next to
  `POST /api/v2/module/<id>/enable`: the module administration (`admin/module/list`,
  `InstalledModuleList`) is still server-rendered. Moves when it becomes an island;
  `POST …/disable` joins then.

## Islands & extension APIs

- **Richtext for the client-rendered model**: a markdown-it plugin extension API and one
  render path for stream entries; `EVENT_AFTER_RUN`/`EVENT_AFTER_OUTPUT` do not fire on the
  JSON path (see [module-migrate-1.20.md](module-migrate-1.20.md)).
- **Data-level menu API** for the content context menu, once stream entries are islands and
  the server-side widget stack of `WallEntryControls` is no longer rendered. Removes the
  deprecated HTML fallback of `GET /api/v2/content/<id>/controls`.
- **Presence** as its own component, driven by live events (the live poll already refreshes
  the caller's own status) — replaces the removed `online` field of the user shape.
- **Dynamic imports** for heavy components, and a **component override** mechanism for themes
  and modules (see [Extending islands](ui-js-vuejs-extensions.md#component-override)).

## Directory pages

- **People on the card directory kit** — `CardDirectory` with a `FilterSet`, as the spaces
  directory has it; People needs a list endpoint (profile-field filters included, as
  `PeopleFilters` builds them) and friendship/follow states through `itemStates`. Then
  `humhub\widgets\DirectoryFilters` and `humhub.cards.js` can be deprecated.

## Core bugs found along the way

Separate PRs: the `AssetBundle` `defaultDepends` typo, the `additions.extend()` `applyOnInit`
string bug, the selector-less timeago addition registration.
