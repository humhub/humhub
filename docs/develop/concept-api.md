# HTTP API framework

**Since 1.19.** The HTTP API *framework* lives in core (`humhub\components\api\`), so core
UI — the [Vue.js islands](ui-js-vuejs.md) — can depend on API endpoints being present. The
optional [`humhub/rest`](https://github.com/humhub/rest) module keeps everything that is
genuinely integration territory: token authentication, its admin UI, and the `/api/v1`
surface.

Core endpoints answer under `/api/v2`. The comment and like islands consume them (see the
[Vue.js roadmap](ui-js-vuejs-roadmap.md)); before 1.19 they consumed the module's `/api/v1`
through browser-session authentication.

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
protected bool $enableSessionAuth = false;
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
  to API requests (2FA, legal, onboarding). `SessionAuth` sets an explicit
  `humhub\components\Request::$isSessionAuthenticated` flag instead, which `GateFilter`
  consults: such requests are classified like browser requests and pass through the normal
  gates. No re-running of the gate lookup, no workaround.
- **Impersonation.** `Impersonation::isActive()` short-circuited while `enableSession` was
  off, so the private-content restriction could not apply to a session-authenticated
  impersonation. It consults the same request flag now, so impersonation restrictions apply
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
  the off-prefix guard takes the request from there.

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
[humhub/rest#248](https://github.com/humhub/rest/issues/248):

- **Timestamps** are ISO-8601 with offset, in UTC (`Format::dateTime()`), instead of
  DB-format strings without timezone.
- **Field names** are camelCase throughout — including the keys of validation errors, which
  are the camelCased attribute names (`Format::attribute()`), so a client matches them
  against the field names it sent rather than against column names.
- **Errors** are plain HTTP status codes with Yii's JSON error body; there is no
  `{code, message}` success/failure envelope. Validation failures are
  `422 {"errors": {attribute: [messages]}}`, a successful delete is `204` with no body.
- **List responses** use one envelope: `{results, total, page, pageSize, pages}`.

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

**The `rest` module.** Requires core 1.19 (`humhub.minVersion`), contributes its
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

The endpoint documentation lives as Swagger sources in the module — `/api/v1` as the flat
files in `docs/swagger/`, the core endpoints under `docs/swagger/v2/` (one document per
module, rendered to `docs/html/v2/`). That is acceptable while this is young, but not as a
shipped state: a core-only installation would have no documentation for its own
endpoints, and docs would sit in a different repository than the code they describe, with
guaranteed drift. The target is core shipping the Swagger sources for its endpoints, the
module for its own, and the documentation build merging both — the `v2/` directory in the
module is deliberately shaped so that becomes a move, not a rewrite.

## Security invariants

These must hold — each one exists because it was found missing:

1. No API action is reachable outside the API URL prefix
   (`BaseController::beforeAction()`, checked before authentication runs).
2. A mutating action is never reachable on a safe HTTP method (rule verbs + `VerbFilter`).
3. Session-authenticated state-changing requests require a valid CSRF token, and no API
   response ever mints a `_csrf` cookie (`SessionAuth`).
4. Session-authenticated requests are subject to the same user gates as a browser request
   (2FA and friends), and are never classified as API requests
   (`Request::$isSessionAuthenticated`, `GateFilter`).
5. Session authentication is off unless a controller opts in
   (`BaseController::$enableSessionAuth`).
6. Session-authenticated impersonation cannot see private content the web UI hides
   (`Impersonation::isActive()`).

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
