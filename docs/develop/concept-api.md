# HTTP API framework

**Since 1.20.** The HTTP API *framework* lives in core (`humhub\components\api\`), so core
UI — the [Vue.js islands](ui-js-vuejs.md) — can depend on API endpoints being present. The
optional [`humhub/rest`](https://github.com/humhub/rest) module keeps everything that is
genuinely integration territory: token authentication, its admin UI, and the `/api/v1`
surface.

Core endpoints answer under `/api/v2`. The comment and like islands consume them (see the
[Vue.js roadmap](ui-js-vuejs-roadmap.md)); before 1.20 they consumed the module's `/api/v1`
through browser-session authentication.

## Status

`/api/v2` is **internal use only** for now: it exists to serve the platform's own UI, and
while the surface is being completed an endpoint or a shape may still change between releases
without a deprecation period. The reference says so in its introduction, and it is why the
contract is being tightened now ("Conventions of `/api/v2`" below) rather than after a first
external client depends on it.

The goal is a complete, stable API that third parties can build on. A resource declared
stable changes additively only — no removed or renamed fields, enums that may grow and that
clients must tolerate, breaking changes only in a new generation. What "declared stable" is
tied to (a release, a per-resource marker in the reference) is decided when the first
resource gets there; until then `/api/v1` remains the integration surface.

## Why

The comment and like islands render entirely client-side and fetch their data from the
API. That makes an API endpoint a hard dependency of core UI — but the API lived in an
optional module, so:

- `humhub\modules\comment\widgets\Comments` had to check whether the `rest` module exists
  before it could embed the island's initial data window.
- Without the module there was no comment UI at all, which is not a defensible state for a
  core feature.
- The module defined what a *comment* looks like on the wire
  (`humhub\modules\rest\definitions\CommentDefinitions`) — a representation owned by the
  wrong module.

## The two layers

**Core** owns the framework and the endpoints that serve core UI:

- `humhub\components\api\` — `BaseController` (request/response conventions, the
  authentication pipeline, the URL-space guard, pagination and validation-error helpers),
  `SessionAuth`, `AuthMethodsEvent`, `SerializeEvent`, `ApiRules`, `Format`.
- `humhub\modules\<module>\controllers\api\` — the endpoints themselves, next to the domain
  code they serve (e.g. `humhub\modules\comment\controllers\api\CommentController`).
- `humhub\modules\<module>\serializers\` — the wire representation of that module's models,
  owned by the module that owns the model (`CommentSerializer`, `UserSerializer`,
  `LikeSerializer`, `FileSerializer`).

**The `rest` module** extends that framework:

- the authentication methods (JWT, Bearer, Query-Param, Basic, Impersonate) plus the user
  allowlist and the admin configuration UI,
- the `/api/v1` surface, and the base class third-party modules already extend.

Deliberately **not** planned: moving `/api/v1` wholesale into core. Its shape conventions
predate this work (DB-format timestamps, mixed naming, a `{code, message}` envelope
alongside plain HTTP errors), token management and the admin UI are module concerns, and a
core-owned v1 would make marketplace compatibility considerably harder.

## Authentication model

Two questions that are easy to conflate — keep them apart:

**1. Which authentication methods exist?** Core ships browser-session authentication only
(`humhub\components\api\SessionAuth`). The module contributes the token methods to *every*
API controller through `BaseController::EVENT_COLLECT_AUTH_METHODS`
(`AuthMethodsEvent`) — a decided point: an installation with the module can call core
endpoints with a token too (the mobile app can read comments), a core-only installation has
a session-authenticated API and no machine-to-machine access.

**2. Which endpoints accept session authentication?** Only those that explicitly opt in —
and that is core's own endpoints.

```php
// humhub\components\api\BaseController
protected bool $allowSessionAuth = false;
```

This default is the security-relevant part of the design, not a formality. Session
authentication deliberately bypasses the module's user allowlist (`enabledForAllUsers` /
`enabledUsers`) — it has to, because the browser UI must work for every logged-in user, and
a session grants nothing the same user cannot already do in the web interface. Third-party
REST controllers, however, were written under the assumption that callers arrive with a
token *and* passed an admin-managed allowlist. If session authentication applied globally,
every one of those endpoints would suddenly be reachable from any logged-in browser
session, with their authorization written for a narrower threat model. Opting in per
controller keeps that from happening by default, and keeps the decision greppable.

A module that later wants a browser-callable endpoint for its own island uses the same flag
and takes responsibility for its own authorization.

Further contracts, all explicit:

- **Ordering.** Contributed token methods run before session authentication; a request
  carrying a valid token authenticates as the token user even with a session cookie
  present. Fall-through is not uniform — only JWT returns `null` on failure and falls
  through, the other token methods throw on an invalid credential, so a malformed token
  yields 401 instead of silently downgrading to the session.
- **CSRF.** Session-authenticated state-changing requests (POST/PUT/PATCH/DELETE) require a
  valid CSRF token (`X-CSRF-Token` header or `_csrf` body param); token-authenticated
  requests stay exempt. `SessionAuth` reads the raw `_csrf` cookie and compares
  timing-safely instead of going through `Request::validateCsrfToken()`, which would mint
  and Set-Cookie a fresh token and clobber the browsing page's own.
- **No admin switch for core endpoints.** Session authentication for core endpoints is not
  optional — the UI depends on it. The module's `enableSessionAuth` setting is gone, which
  returns its `/api/v1` surface to token-only. Net effect: less attack surface than before.
- **Guest access.** The per-controller `$guestAllowedActions` list is honored only while
  guest access is enabled platform-wide, with each action still responsible for its own
  guest-safe authorization (`Content::canView()` and friends).
- **Gate classification.** `humhub\components\gates\GateFilter` used to infer
  `RequestClass::Api` from `Yii::$app->user->enableSession === false`, which every API
  request pins — so a session-authenticated request would skip the gates that do not apply
  to API requests (2FA, legal, onboarding). `SessionAuth` marks the user component instead
  (`humhub\modules\user\components\User::$sessionAuthenticated`), and `GateFilter` asks
  `User::isSessionBased()` — `enableSession` or that mark: such requests are classified like
  browser requests and pass through the normal gates. No re-running of the gate lookup, no
  workaround, and the request component stays untouched.
- **Impersonation.** `Impersonation::isActive()` short-circuited while `enableSession` was
  off, so the private-content restriction could not apply to a session-authenticated
  impersonation. It asks `User::isSessionBased()` too, so impersonation restrictions apply
  normally and such requests need not be rejected.

## Routing and registration

Endpoints declare their rules in the `config.php` of the module they belong to.
`humhub\components\ModuleManager` reads the `urlManagerRules` key from every module's
`config.php` and registers it **prepended** (`addRules($rules, false)`), so module rules
win over the generic fallback routing. `ApiRules::v2()` prefixes the patterns with the
version prefix:

```php
// humhub/modules/comment/config.php
'urlManagerRules' => ApiRules::v2([
    ['pattern' => 'comment/content/<id:\d+>/window', 'route' => 'comment/api/comment/window-by-content', 'verb' => ['GET', 'HEAD']],
    // ...
]),
```

Routes point at the module's own API controllers; Yii resolves controller subdirectories
from the route on its own (`yii\base\Module::createController()` turns
`comment/api/comment/window-by-content` into
`humhub\modules\comment\controllers\api\CommentController::actionWindowByContent()`, no
`controllerMap` entry needed), so the internal route shape stays invisible to clients.

The `rest` module keeps firing its own `restApiAddRules` event, because two third-party
modules (`cuzy-app/cloner`, `cuzy-app/rest-crud`) subscribe to it by its **string name**
precisely so they survive the module being absent.

### API controllers must not be directly callable

API controllers sit in namespaces Yii's fallback routing reaches by default: with no rule
matching, `UrlManager::parseRequest()` treats the path itself as the route, so
`/comment/api/comment/view?id=1` would resolve straight to the API action — outside the API
prefix, and therefore outside everything that prefix implies (CSRF handling for session
requests, verb constraints, the auth pipeline as configured for API traffic). A cross-site
top-level navigation can trigger such a GET with the session cookie attached
(SameSite=Lax).

The load-bearing defence is the base controller, not the rules:

- **`BaseController::beforeAction()` hard-fails any request whose `pathInfo` is not under
  `api/v2/`**, before authentication runs. Checking `pathInfo` rather than matched rules is
  what makes query-param routing fail closed: for `?r=comment/api/comment/view` the
  `pathInfo` is empty, so the check rejects it. A consequence worth stating explicitly is
  that the API surface requires pretty URLs — already true for the module before.
- **Per-action verb constraints** (`VerbFilter`), so a mutating action is never reachable on
  a safe method regardless of what any rule says. Note that a verb mismatch on a
  verb-constrained *rule* produces a 404, not a 405: the rule simply does not match, and
  the off-prefix guard takes the request from there. Either way the answer is a JSON error
  body: `humhub\components\Application::handleRequest()` fixes the response format for
  everything under `api/` before routing (see the conventions below).

There is deliberately **no URL rule** as a second layer. A prepended
`<module>/api/<anything>` → 404 rule looks attractive, but it cannot replace the
controller-level check anyway (query-param routing never consults rules), and it silently
breaks modules whose own web controller happens to be called `api`: `humhub/translation`
serves a public JSON endpoint from `controllers/ApiController` at `/translation/api/index`,
reached through default routing without a rule of its own. A redundant layer is not worth
404-ing someone else's endpoint — the base-controller check is the guarantee.

The module's per-module admin toggle (`apiModules` / `isActiveModule()`, which lets an
administrator switch a module's endpoints off) does **not** extend to core endpoints: they
are part of the UI, not an optional integration surface.

## Caller context is not part of a payload

A payload that depends on WHO is asking cannot be served twice, so none of it is in one.
Everything about a comment - message, author identity, timestamps, files, counts, the reply
preview - is identical for every reader who may see the content; three narrow things are
not, and each has its own place:

| What | Where | Why there |
|---|---|---|
| like state (`total`, `liked`, `canLike`) | `GET like/states?recordIds=…`, batched per window | the only value that is per record AND per caller; batching keeps it at one request and two queries instead of one pair per record |
| `canEdit`/`canDelete` | `GET comment/<id>/permissions`, on context-menu open | needed nowhere else, and the `⋮` trigger renders regardless, so nothing has to be known up front. Deliberately NOT derived client-side: the rule may grow (edit windows, module overrides) and a second implementation would drift |
| the author's online status | a client concern, resolved separately | presence is volatile and per viewer (nobody sees an indicator on their own records) |

Sometimes the caller context IS the resource: `GET space/<id>/membership` answers "what is my
relationship to this space", so its shape is never embedded in a space payload and never
cached — and it carries what the caller may do next (`canJoin`, `canLeave`) for the same
reason `canEdit`/`canDelete` are not derived client-side.

The same rule binds `SerializeEvent` handlers: `extensions` data must be caller-neutral.
A module needing caller-specific state fetches it from its own endpoint, in its own Vue
component or menu entry - it needs an endpoint for the action anyway. That is the same line
already drawn for blocked-author masking, which is entirely client-side.

For the page render this costs nothing: the comment widget embeds the window **and** inlines
the caller's like states for it (the page render is per user anyway), so the first paint makes
no request at all. Only paging, replies, own creates and live updates fetch states, one
request per batch.

What it buys: one serialization per content and window, identical for members and guests, and
`hasLiked` collapsing from one query per comment to one per window. Caching that
serialization - server-side with a TTL, or via ETag on the API - becomes possible; it is
tracked as a follow-up together with the invalidation question (author display names and
profile image URLs live in the body, so a rename or a new avatar is visible only after the
TTL).

### The payloads are cached server-side

Because they are caller-neutral, one serialization serves everyone:
`comment\services\CommentPayloadCache` wraps `CommentSerializer::window()`/`comment()` and is
what the comment widget and the API controller call.

- **Key**: content id + a per-content invalidation token + the language + what distinguishes
  the payload within its content (parent comment, cursor, direction, page size, limit).
- **Invalidation**: `Comment::afterSave()`/`afterDelete()` replace the content's token, which
  retires all of its entries at once - no key enumeration for the arbitrarily many windows a
  content has. A random token rather than a counter, so two concurrent invalidations cannot
  settle on the same value and resurrect what they just retired.
- **Not invalidated**, and therefore only as fresh as the TTL (`comment` module's
  `payloadCacheTtl`, default one hour, `0` disables): the author display name and profile
  image URL the payload embeds, data modules attach through `SerializeEvent`, and a file
  detached from a comment without touching the comment itself.
- **Authorization is not cached.** The cache is keyed by content, never by caller; every
  request still passes `Content::canView()` and the `guestHideComments` check before anything
  is read from it, so a hit can never widen access. This holds because
  `CommentListService` does no per-caller filtering either - the comments of a content are
  visible to whoever may view the content.

Measured (SELECTs, isolated from the rest of the request):

| Window | serializing | cache hit |
|---|---|---|
| wall/stream overview (2 roots + their reply previews = 4 comments) | 21 | **0** |
| detail view (19 comments) | 77 | **0** |

That is ~4-5 queries per serialized comment - child counts, attached files and the rich-text
extension pipeline - and a stream page pays it per entry. What stays per request is the
caller-specific part, and it is flat rather than per comment: resolving the window's records
plus the two grouped like queries, 5 SELECTs for 4 as well as for 19 comments.

## Serialization ownership

Each module serializes its own models. The comment module owns the comment representation,
the user module the user representation, and so on. A serializer is a plain class with
static methods returning arrays; controllers do not define shapes themselves.

The batch extension event lives in core as `humhub\components\api\SerializeEvent`: fired
once per response for each batch of records of one type, so modules can attach namespaced
data to individual records without N+1 queries (see
[Vue.js extensions](ui-js-vuejs-extensions.md), "Serializer extension events").

The module's v1 definitions are still their own implementation — reimplementing them over
the core serializers (so there is exactly one serializer per model) is a follow-up, not
part of this step.

## Conventions of `/api/v2`

The version expresses a **contract generation, not a code location**. The conventions this
generation fixes are the ones collected in
[humhub/rest#248](https://github.com/humhub/rest/issues/248), completed by what settled while
the first endpoints were built:

- **Everything under `api/` answers JSON**, errors included.
  `humhub\components\Application::handleRequest()` fixes the response format before routing,
  so an unknown route, a verb no rule was registered for and an exception a controller throws
  before `BaseController::beforeAction()` ran all render as Yii's JSON error body — never the
  HTML error page. `yii\web\ErrorHandler::renderException()` decides by the format the
  response has when the exception arrives, which is why this cannot live in the controller.
- **Timestamps** are ISO-8601 with offset, in UTC (`Format::dateTime()`), instead of
  DB-format strings without timezone.
- **Field names** are camelCase throughout — including the keys of validation errors, which
  are the camelCased attribute names (`Format::attribute()`), so a client matches them
  against the field names it sent rather than against column names.
- **Enums are named values** (`state: member`, `visibility: public`), never the stored
  integers.
- **Records are addressed by numeric id** — the record's own id in a path (`/space/<id>`,
  `/comment/<id>`), the platform-wide `recordId` for anything likeable, the content container
  id (`containerId`) where a space or user is meant as a container. Guids appear in payloads
  for display and URLs, never as an address, and class names never reach the wire (`model` +
  `pk` addressing is a v1 thing).
- **Where a parameter travels follows the verb.** What identifies or filters a read goes in
  the query string; what a `POST` creates and what a `PATCH` changes travels in the JSON
  body, the target included (`POST /comment {contentId, message}`, `POST /like {recordId}`);
  a `DELETE` carries its options in the query string (`DELETE /comment/<id>?notify=1`),
  because a `DELETE` body is dropped by enough clients and proxies not to build on.
- **`PATCH` is the one update verb**, and it is partial: a field the body does not carry
  keeps its value. There is no `PUT` — the implementation (`Model::load()` + `save()`) has
  always been partial, and one verb whose documentation matches beats two aliases.
- **Errors** are plain HTTP status codes with Yii's JSON error body; there is no
  `{code, message}` success/failure envelope. Validation failures — a missing required
  parameter included (`BaseController::missingParameter()`) — are
  `422 {"errors": {attribute: [messages]}}`. `403` is reserved for what the caller may not
  do, never for a state the caller is already in.
- **Status codes of writes**: creating a resource answers `201` with the resource
  (`POST /comment`), a state transition answers `200` with the new state (`POST /like`,
  `POST /space/<id>/membership`), a successful delete `204` with no body. The file upload is
  the one exception, see below.
- **Affirming and removing are idempotent.** `POST` on a state that is already affirmed (a
  member joining again, liking what is liked, re-sending a friendship request) and `DELETE`
  on one that is already gone both succeed and answer the current state, so a client acting
  on a stale view ends up with the truth rather than an error.
- **Lists** come in three shapes, each chosen by how the list behaves while it is read:
  - **offset pages** — `page`/`pageSize` in, `{results, total, page, pageSize, pages}` out
    (`BaseController::handlePagination()`/`returnPagination()`) — for lists that hold still
    (`like/users`, `space`);
  - **cursor pages** — `cursor`/`limit` in, `{results, nextCursor}` out — for lists that
    reorder while being read: `activity` (groups form and re-key) and `notification` (ordered
    unseen-first, reorders as notifications arrive and are read; `unseenCount` rides along
    because every consumer needs it for its badge). Page numbers would skip or repeat entries
    there;
  - **comment windows** — `cursor` + `direction`, or `focus`, plus `limit` in,
    `{results, total, rootTotal, prevCount, nextCount}` out — because a thread is paged in
    both directions from a comment the client already shows, and needs the exact remaining
    counts a "show previous/next N comments" UI renders.

One documented exception exists, and it is about a batch rather than a field: `POST /api/v2/file`
(the endpoint the Vue upload field posts to) carries any number of files in one request and
answers per file —

```json
{ "results": [ <file>, … ], "errors": [ { "fileName": "big.pdf", "messages": ["…"] } ] }
```

— with `200` whenever the request carried at least one file, even if every single one was
rejected. One invalid file among ten must not discard the nine the user picked alongside it, and
a client rendering per-file errors takes the same code path either way. A request carrying no
file at all IS a malformed request and answers the usual `422 {"errors": {"files": […]}}`.

A concrete payoff: the islands' adapter layer
(`comment/vue/components/commentApi.js`) used to parse DB timestamps against the announced
server timezone and map snake_case user shapes. It now only adds what a client is *supposed*
to derive — `isEdited` (`updatedAt !== createdAt`), the blocked-author flag from the
viewer's own block list, and `canAdminDelete` (`canDelete` on someone else's comment).

## Migration path

**Third-party modules.** 13 module repositories extend
`humhub\modules\rest\components\BaseController` — among them `humhub/mail`,
`humhub/cfiles`, `humhub/wiki`, `humhub/tasks`, `humhub/legal`, plus several `cuzy-app`
modules. That base class stays, so those modules need **no change at all** — not even a
version bump. They depend on the module providing that class, not on any core API, and
`module.json` has no mechanism to express a dependency on another module's version anyway.
Their endpoints keep behaving exactly as before: reached with a token, gated by the
allowlist, and never reachable from a browser session.

The obligation this creates is on our side: the module base class's protected surface
(`behaviors()`, `beforeAction()`, `isUserEnabled()`, `handlePagination()`,
`returnPagination()`, `returnError()`, `returnSuccess()`, `$guestAllowedActions`) must stay
signature-compatible, since third-party controllers override and call into it. Letting it
become a subclass of the core base controller is a follow-up that has to preserve exactly
that surface, the `api/v1/` prefix guard and the module's error envelope.

**The `rest` module.** Requires core 1.20 (`humhub.minVersion`), contributes its
authentication methods to the core chain, and dropped its own session authentication (the
`SessionAuth` class and the `enableSessionAuth` setting) — see the module's
`docs/api-stack.md`. The old module line needs a `humhub.maxVersion` so an outdated version
cannot run next to the core framework and register duplicate rules.

Worth knowing when relying on those bounds: `humhub.minVersion`/`maxVersion` are
**marketplace metadata**, not runtime enforcement — core does not evaluate them when loading
a module, the marketplace uses them to decide what it offers for a given core version. An
administrator who copies an outdated module into a new installation by hand bypasses them
entirely.

## Documentation

Core documents its own endpoints, in the same repository as the code they describe:

```
docs/api/
├── index.html         # the rendered reference: every source joined into one page (committed)
├── build.sh           # lints src/*.yaml, joins them and renders index.html
├── redocly.yaml       # lint rule set and Redoc options (theme, logo size)
├── template.hbs       # the renderer's page template, plus the sidebar logo's "API Reference" caption
└── src/*.yaml         # OpenAPI 3.1 sources — `index.yaml` the introduction, `common.yaml` the shared components
```

One source per module owning endpoints (`account`, `activity`, `comment`, `content`, `file`,
`friendship`, `like`, `notification`, `space`), each tagging its operations with the module's
name and introducing the module in that tag's description; `src/index.yaml` carries the general
introduction and the conventions, `src/common.yaml` the shared schemas, parameters, error
responses and security schemes. `build.sh` joins them into one document (`redocly join`) and
renders it as `index.html` — one page with a sidebar across all modules. The rendered page is
**committed**, so the reference opens straight from a checkout, no server needed, without a
build step; `docs/api/build.sh` (or `grunt build-api-docs`) re-renders it after a source
change, and the result belongs in the same commit. It is a repository document, not a served
page: since the `public/` document root (#8459) the checkout's `docs/` directory is not
web-accessible.

The build lints the sources first, with Redocly's `recommended-strict` rule set
(`docs/api/redocly.yaml`): every warning is an error, so a schema that renders fine but is
invalid — `nullable` without a type, say — or an operation without an `operationId` stops the
build. Every operation carries an `operationId` in `verbResource` form (`getComment`,
`listSpaces`, `affirmSpaceMembership`, `uploadFiles`), which is what client generators name
their methods after. `.github/workflows/api-docs.yml` runs the same build on every change
under `docs/api/` and fails when the committed page differs from the re-rendered one — the
guarantee `js-test.yml` gives for the Vue build artifacts, for the reference.

A rendered page loads only from its own origin: webfonts are disabled, and the renderer's two
Redocly CDN assets — the Redoc bundle itself and the "API docs by Redocly" badge the bundle
requests at runtime — are vendored next to the pages (`redoc.standalone.js` ~1 MB,
`redoc-logo-mini.svg`, plus the bundle's license notice). Without that, a page stays empty
without internet access or behind a CSP that only allows its own origin, and every reader of an
installation's own documentation would be announced to a third party. `build.sh` re-downloads
the bundle whenever the renderer's Redoc version changes, verified against the integrity hash
the renderer emits (recorded in `redoc.standalone.js.sha384`) before the one logo URL inside it
is rewritten to the vendored SVG.

The `rest` module documents its own `/api/v1` surface the same way, in its own repository.
The three list shapes and the per-file batch answer of the upload endpoint are explained in
the conventions above; the documents assume them.

## Security invariants

These must hold — each one exists because it was found missing:

1. No API action is reachable outside the API URL prefix
   (`BaseController::beforeAction()`, checked before authentication runs).
2. A mutating action is never reachable on a safe HTTP method (rule verbs + `VerbFilter`).
3. Session-authenticated state-changing requests require a valid CSRF token, and no API
   response ever mints a `_csrf` cookie (`SessionAuth`).
4. Session-authenticated requests are subject to the same user gates as a browser request
   (2FA and friends), and are never classified as API requests
   (`User::isSessionBased()`, `GateFilter`).
5. Session authentication is off unless a controller opts in
   (`BaseController::$allowSessionAuth`).
6. Session-authenticated impersonation cannot see private content the web UI hides
   (`Impersonation::isActive()`).
7. Everything under the API URL prefix answers JSON, errors included — an unknown route or a
   wrong verb never renders the HTML error page (`Application::handleRequest()`).

## Open points

- **HTTP caching:** the payloads would also survive an `ETag`/`Last-Modified` round trip, and
  guest-visible content could even be cached by a shared cache. Core ships no HTTP-cache
  infrastructure for this yet (only `file\actions\DownloadAction` uses `HttpCache`).
- **Query batching for the rest of the payload:** `childCount` and the attached files are
  still one query per comment on a cache miss. The same preload idea as for the like state
  applies, now that the caller-specific queries are gone.
- **Rate limiting:** always-available, session-reachable endpoints multiply request volume
  from every logged-in browser. Yii's `yii\filters\RateLimiter` needs the identity to
  implement `RateLimitInterface`; a cache-based throttle in the base controller is the
  lighter alternative. Decide before this leaves beta.
- **`/api/v1` over the core stack:** the module's base controller becoming a subclass of the
  core one, and its definitions becoming a compatibility layer over the core serializers.
- **Core-shipped Swagger:** see "Documentation" above.
- **Lifetime of `/api/v1`:** kept indefinitely as a compatibility surface, or with a
  deprecation horizon?
