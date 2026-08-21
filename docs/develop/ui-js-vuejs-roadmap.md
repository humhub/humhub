# Vue.js Integration — Status & Roadmap

Short living document tracking where the Vue islands initiative stands and what is
planned next. Detailed concepts live in the [chapter docs](ui-js-vuejs.md); this page
is intentionally brief.

## Status

The work happens on the long-running branch `enh/vuejs-integration`, which stays
independent of `develop` for now (deliberate decision: gather more real-world Vue
experience before merging).

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
  (`SerializeCommentsEvent`).
- **Pilots** — LikeButton (incl. Vue user-list modal fed by a JSON endpoint) and
  the full comment section (client-rendered from JSON, live updates, editing,
  cursor-window pagination), plus `UserImage`/`UserList` as module-provided shared
  components in the user module.

## Next: REST API convergence

The comment/like JSON endpoints should converge onto the official `rest` module
(`/api/v1`) as the single canonical API. Plan:

1. **Session auth in the rest module** (branch `vue` in the rest repo): accept the
   normal browser session — with mandatory CSRF for state-changing requests — so
   the islands can call `/api/v1` directly. The rest module stays permanently
   enabled on the development installation during this experiment.
2. **Endpoint convergence**: extend the rest endpoints (or add internal ones) with
   what the islands need — window pagination (`prevCount`/`nextCount`/`rootTotal`,
   cursor), viewer context (`canEdit`/`canDelete`), the `extensions` namespace,
   `message` + `messageRenderOptions`, like info/user-list. Core services
   (`CommentJsonService`, `UserJsonService`) remain the single source of truth;
   rest controllers become thin wrappers.
3. **Target**: the like/comment modules keep no own JSON controllers — the islands
   speak only the REST API (UI-only HTML actions such as the admin-delete modal
   stay in core). The core↔module dependency question is resolved before any
   merge (move the REST framework into core, or bundle the module).

## Backlog

- Client-side like cache (count-only init, no per-record state requests).
- Fully cacheable comments response: move `canEdit`/`canDelete` and other
  viewer-specific data into a lazily loaded viewer context (later also relevant
  for stream entries).
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
