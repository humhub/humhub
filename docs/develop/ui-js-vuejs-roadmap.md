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
  `grunt build-vue` (`--module all` builds every one of them), vitest test
  infrastructure, `.vue` message extraction, and a CI job running the suite plus an
  artifact-freshness check.
- **Core component set** (`protected/humhub/vue/`) — `RichTextOutput`,
  `LegacyFormWrapper`, `DropdownMenu`, `ExtensionSlot`, `UiModal`, `StatusBar`, plus the
  form suite (`HumHubForm`, `TextField`/`TextareaField`/`CheckboxField`/`SelectField`,
  `UploadField`, `SubmitButton`, `RichTextField`).
- **Extension APIs** — reactive extension slots, the menu-entry registry
  (`registerMenuEntry`/`removeMenuEntry`), and the batch serializer event
  (`humhub\components\api\SerializeEvent`).
- **Pilots** — LikeButton (incl. Vue user-list modal fed by a JSON endpoint) and
  the full comment section (client-rendered from JSON, live updates, editing,
  cursor-window pagination), plus `UserImage`/`UserList` as module-provided shared
  components in the user module.
- **Status bar** — the platform's user-feedback bar is an island (`StatusBar`), driven
  through a bridge-level queue so the legacy `ui.status` API and the `POS_END` flash-message
  snippet keep working untouched. First infrastructure island: no props, no consumers, and
  every caller stays where it is.
- **Native file uploads** — `UploadField` (form suite) on the new `POST /api/v2/file` /
  `DELETE /api/v2/file/<id>` endpoints, so a form shell carries only the richtext editor;
  contributed file handlers keep working as server-rendered dropdown entries.
- **Notifications** — the top-menu dropdown and the overview page are islands
  (`NotificationMenu`, `NotificationOverview`) over `GET /api/v2/notification` and
  `POST /api/v2/notification/mark-as-seen`, sharing one `NotificationList`. Entries render
  client-side around the server's own sentence (`BaseNotification::html()`); the badge, the
  document title and live arrivals are Vue state, and the `mail` module keeps its two legacy
  events. `humhub.notification.js` is gone. `SpaceImage` joined the space module as a shared
  component for the space badge.
- **Space membership** — the membership button is an island (`MembershipButton`) over
  `GET|POST|DELETE /api/v2/space/<id>/membership`, including its request-membership dialog
  (native `UiModal` + form suite). Presentation moved from a per-button option array to
  props, which retires the option round trip through the client that #8381/#8382 had to
  harden: the server re-rendered the button after every transition, so the button's own
  presentation had to be posted back to it.
- **Friendship** — the friendship button is an island (`FriendshipButton`) over
  `GET|POST|DELETE /api/v2/user/<id>/friendship`, built the same way. With it the option round
  trip is gone from the platform entirely: `content.container.relationship` and its
  `data-button-options` posting had no users left and were removed.
- **Spaces** — the space menu of the top navigation is an island (`SpaceChooser`, plus the
  small `SpaceChooserToggle` inside the menu button, because the topbar styles that button with
  child selectors). It reads the platform's new general space list, `GET /api/v2/space`, which
  is caller-neutral on purpose so a picker or a directory can read the same shape; what the
  caller is to a space — member, follower, unseen items — comes from `GET /api/v2/space/states`
  for the spaces displayed, the way `like/states` batches like state. One search field now
  covers both the caller's own spaces and every space they may see, so the second legacy route
  is gone, and the list is paginated instead of loading every membership at once.

- **Activities** — the "Latest activities" box is an island (`ActivityBox`) over
  `GET /api/v2/activity`, and the first one that owns its whole panel: the widget renders only
  the mount point, the first page and the server-rendered `PanelMenu`. Grouping stays in the
  query; an entry reports how many activities it stands for and pages by an opaque cursor over
  the grouping key, which the entry's own id cannot serve as. It is also the first island with
  live updates of its own (`activity\live\NewActivity`): a new activity has the box read its
  head again, entries it already shows are refreshed where they stand, and genuinely new ones
  wait until the list is scrolled to the top so nothing jumps under the reader. `humhub.activity.js`
  and its `niceScroll` scrollbar are gone.

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
- Impersonate-token restriction: core 1.19 hides private content while impersonating, but
  that state is session-bound, so an impersonate **token** (rest module) still bypasses it.
  Parked by owner decision.

### Release & housekeeping

- `humhub.maxVersion` on the previous `rest` module line, so the marketplace stops offering a
  version without the core API stack for 1.20+. (0.13 requires 1.20 for exactly that
  reason; the released 0.12.x additionally crashes on impersonate-token auth on 1.19+,
  core #8372.)

### Islands & tooling

- Rethink richtext rendering/extension architecture for the client-rendered
  model (markdown-it plugin extension API, client-side oembed fetch, unified
  render path for stream entries; currently `EVENT_AFTER_RUN`/`EVENT_AFTER_OUTPUT`
  do not fire on the JSON path — see `module-migrate.md`).
- Module migrations onto the new extension APIs: reportcontent, reaction
  (menu entries), legal, linkpreview, translator (richtext output events).
- Core bug follow-ups discovered along the way (separate PRs): `AssetBundle`
  `defaultDepends` typo, `additions.extend()` applyOnInit string bug,
  selector-less timeago addition registration.
