# Vue.js Integration — Status & Roadmap

Short living document tracking where the Vue islands initiative stands and what is
planned next. Detailed concepts live in the [chapter docs](ui-js-vuejs.md); this page
is intentionally brief.

## Status

The work happens on the long-running branch `enh/vuejs-integration`, open as draft
[#8403](https://github.com/humhub/humhub/pull/8403) with the `rest` module companion
[humhub/rest#249](https://github.com/humhub/rest/pull/249). It stays independent of `develop`
for now (deliberate decision: gather more real-world Vue experience before merging).

Done on that branch:

- **Runtime & tooling** — `humhub.vue` island runtime (registry, mounter,
  PJAX/modal lifecycle via MutationObserver), committed build artifacts via
  `grunt build-vue`, vitest test infrastructure, `.vue` message extraction.
- **Core component set** (`protected/humhub/vue/`) — `RichTextOutput`,
  `LegacyFormWrapper`, `DropdownMenu`, `ExtensionSlot`, `UiModal`, plus the form
  suite (`HumHubForm`, `TextField`/`TextareaField`/`CheckboxField`/`SelectField`,
  `SubmitButton`, `RichTextField`).
- **Extension APIs** — reactive extension slots, the menu-entry registry
  (`registerMenuEntry`/`removeMenuEntry`), and the batch serializer event
  (`humhub\components\api\SerializeEvent`).
- **Pilots** — LikeButton (incl. Vue user-list modal fed by a JSON endpoint) and
  the full comment section (client-rendered from JSON, live updates, editing,
  cursor-window pagination), plus `UserImage`/`UserList` as module-provided shared
  components in the user module.

## Done: the islands run on the platform API

The comment/like islands consume `/api/v2`, the HTTP API core itself ships — core
keeps no own JSON controllers for them anymore (the comment `show` popup mode is
the one remaining UI-only HTML action):

1. **API framework in core** (`humhub\components\api\`): base controller,
   request/response conventions, URL-space guards, the batch serialize event and
   browser-session authentication (opt-in per controller, CSRF-checked for
   state-changing requests). Endpoints live next to the module that owns them
   (`humhub\modules\<module>\controllers\api\`), wire shapes in that module's
   `serializers\`. See [HTTP API framework](concept-api.md).
2. **One documented contract**, in v2 conventions (ISO-8601 UTC timestamps,
   camelCase, plain HTTP status codes, `422 {"errors": …}`): window pagination
   (`GET comment/content/<id>/window`, `prevCount`/`nextCount`/`rootTotal`), the
   `extensions` namespace, `message` + `messageRenderOptions`, structured `files`, like
   state/toggle/users, and `account`/`account/blocked-users`. Caller-dependent values have
   their own endpoints (see the next section). The islands derive client-side what a client
   can derive (`isEdited`, admin-delete capability, blocked-author masking) and parse ISO
   timestamps natively — the old adapter layer is gone.
3. **The `rest` module** keeps `/api/v1` and contributes its token authentication
   methods to the core endpoints (`EVENT_COLLECT_AUTH_METHODS`); its own session
   authentication was removed, so `/api/v1` is token-only again. See the module's
   `docs/api-stack.md`.
## Done: cacheable comment payloads

The comment payload carries nothing that depends on who is asking, so one serialization
serves every reader (and can be cached):

- **Like state** — `GET like/states?recordIds=…`, one batched request per loaded window
  (the widget inlines the states of the embedded window, so the first paint needs none).
  `hasLiked` went from one query per comment to one per window.
- **`canEdit`/`canDelete`** — `GET comment/<id>/permissions`, loaded when an entry's context
  menu opens, with a loader in the menu. Deliberately not re-implemented client-side.
- **Presence** — `online` left the user shape; the online dot is gone from comment avatars
  until it becomes its own live-driven component (see the backlog).
- **Server-side cache** — `comment\services\CommentPayloadCache` caches windows and single
  comments per content, retired instantly by a per-content token whenever a comment changes;
  the `payloadCacheTtl` module setting only bounds staleness of what the payload embeds
  without owning (author name/avatar, module `extensions` data). Measured: serializing a
  19-comment detail window costs 77 SELECTs, a cache hit 0; the overview window (4 comments)
  21 vs 0. The caller-specific like states stay uncached but are flat at 5 SELECTs per
  window.

See [HTTP API framework](concept-api.md), "Caller context is not part of a payload".

## Backlog

Everything parked or deferred lives here; the reasoning is in
[HTTP API framework](concept-api.md) and, for the module side, in the `rest` module's
`docs/api-stack.md`.

### API & performance

- Query batching for the rest of the payload (`childCount`, files) on a cache miss — would
  take a 19-comment window from 77 SELECTs to roughly ten.
- HTTP caching on top of the server-side cache (ETag/Last-Modified, shared caches for
  guest-visible content) — core has no infrastructure for it yet.
- Presence as its own component, driven by live events (the live poll already refreshes the
  caller's own status) — replaces the removed `online` field, which the comment avatars and
  the like user list both lost.
- Rate limiting for the session-reachable endpoints: always-available API endpoints multiply
  request volume from every logged-in browser. Decide before this leaves beta.
- `/api/v1` over the core stack: the `rest` module's base controller becoming a subclass of
  the core one, its definitions a compatibility layer over the core serializers.
- Core-shipped Swagger sources — the v2 documents currently live in the `rest` module under
  `docs/swagger/v2/`, deliberately in their own directory so the move is a `git mv`.
- Impersonate-token restriction: core 1.19 hides private content while impersonating, but
  that state is session-bound, so an impersonate **token** (rest module) still bypasses it.
  Parked by owner decision.

### Release & housekeeping

- `humhub.maxVersion` on the previous `rest` module line, so the marketplace stops offering a
  version without the core API stack for 1.19+. (0.13 requires 1.19 already; the released
  0.12.x additionally crashes on impersonate-token auth there, core #8372.)

### Islands & tooling

- Rethink richtext rendering/extension architecture for the client-rendered
  model (markdown-it plugin extension API, client-side oembed fetch, unified
  render path for stream entries; currently `EVENT_AFTER_RUN`/`EVENT_AFTER_OUTPUT`
  do not fire on the JSON path — see `module-migrate.md`).
- `UploadField` as a standalone form-suite field (requires splitting the
  server-rendered form shell into per-field fragments).
- Module migrations onto the new extension APIs: reportcontent, reaction
  (menu entries), legal, linkpreview, translator (richtext output events).
- CI: vitest job + committed-artifact freshness check.
- Core bug follow-ups discovered along the way (separate PRs): `AssetBundle`
  `defaultDepends` typo, `additions.extend()` applyOnInit string bug,
  selector-less timeago addition registration.
