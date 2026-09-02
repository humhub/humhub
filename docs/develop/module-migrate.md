# Module Migration Guide

Migration notes for keeping a module compatible with newer HumHub core releases.

Each minor release line has its own file with the breaking changes, new APIs and deprecations. Notes for the **current development cycle** live in the `Unreleased` section below — pull requests that introduce breaking changes should add an entry here, not in a release-specific file.

## Unreleased

- Only one Select2 build is served now. `kartik\select2\Select2Asset` is emptied through the
  asset manager configuration and depends on `humhub\assets\Select2Asset` instead, so Krajee
  widgets (`kartik\select2\Select2` and subclasses such as `humhub\modules\ui\form\widgets\IconPicker`)
  no longer publish their own copy from the `select2/select2` package. Modules using such a widget
  now get the core Select2 version (4.0) instead of the one that package resolves to (4.1) — check
  for usages of Select2 4.1-only options, DOM or CSS classes.
  **A module must never register a Select2 build of its own** — always depend on
  `humhub\assets\Select2Asset`. A second build replaces `$.fn.select2.amd` while `$.fn.select2`
  keeps running the first one, so the two disagree: the core dropdown addition resolves its
  dropdown adapter from that registry and would be handed classes from the foreign version,
  which breaks every `data-ui-select2` dropdown on the page. For the same reason, do not resolve
  Select2 internals from `$.fn.select2.amd` in module JS.

- Added a **describable menu entry** API (`humhub\modules\ui\menu\MenuEntry::describe()`,
  the `humhub\modules\ui\menu\DescribableWidget` interface) plus the
  `humhub\modules\content\vue\ContentControls` island and its
  `GET /api/v2/content/<id>/controls` endpoint — the content context menu (`WallEntryControls`)
  rendered by a client instead of the server. Purely additive: `WallEntryControls::EVENT_INIT`
  is unchanged, and it is still the way to contribute an entry.
  - `WallEntryControlLink` implements `DescribableWidget`, so every control link extending it
    (`EditPageLink` in wiki, `ShareLink` in share-between-humhub, `ContentTopicButton` in core)
    is described without any module change.
  - **Deprecated**: contributing a menu entry whose widget cannot describe itself. Such an
    entry is still rendered server-side and delivered as raw HTML, so nothing breaks today,
    but it cannot be conditioned, overridden or removed by a client, and every delivery logs
    a warning naming the widget class. A subclass of `WallEntryControlLink` that overrides
    `renderLink()` is deliberately in this group unless it also overrides
    `describeMenuEntry()` — describing it from the base class' properties would produce an
    empty label or a dead `#` link. See `docs/develop/ui-js-vuejs-extensions.md`,
    "Server-described entries and `ContentControls`".
  - The Vue `DropdownMenu` entry descriptor grew `url`, `htmlOptions`, `divider` and `html`.
    Existing entries are unaffected.
  - `DropdownMenu` grew a `rootClass` prop and a `toggle` slot, both defaulting to the previous
    markup. Needed because its root is hard-coded `.nav nav-pills preferences`, which
    `_nav.scss` fills with the primary colour and positions absolutely — correct for a
    content-controls menu, wrong for a labelled dropdown button.
  - **Fixed**: `_list.scss` no longer colours `a.dropdown-item` inside an `.hh-list` row. Its
    rule tied with `_nav.scss`'s menu-item colour (both 0,2,2) and won on import order, so a
    dropdown rendered inside a list was painted with the list's text colour on the menu's own
    background. Only affects anchors carrying `.dropdown-item`; ordinary row links are
    unchanged.

- Added the **Vue.js island layer** (`humhub.vue` client registry/mounter, `humhub\widgets\VueComponent`,
  `grunt build-vue` tooling) — see `docs/develop/ui-js-vuejs.md`. Purely additive; the existing
  `humhub.module` JS system is unaffected.
  - Also added the standalone core JS module `url` (`humhub.url.js`) — the client-side counterpart
    of `yii\helpers\Url::to()` for default-routed endpoints, usable from any `humhub.module()` via
    `require('url').to(route, params)`. Purely additive; `@humhub/vue`'s `url()` now delegates to it.
  - The publish excludes for `@humhub/resources` bundles (`scss/`, `.gitignore` — introduced
    with #8277) were never applied at runtime due to an alias comparison bug and are now
    active; `build/` is deliberately **not** excluded because the compiled `humhub-app.css`
    references it via relative `url()`.
  - **Removed**: the client-side `like` JS module (`humhub.like.js`, `like.toggleLike`) and
    `humhub\modules\like\assets\LikeAsset` — the like link is now a self-contained Vue island
    (`humhub\modules\like\vue\LikeButton.vue`, `LikeVueAsset`) that builds its own URLs and
    labels client-side. The `humhub:like:liked` DOM event is still fired on like, unchanged.
    The `likeLinkContainer_<id>` wrapper element id no longer exists — the like-user-list modal
    covers the same "who liked this" info the old title tooltip provided. Known affected: a CSS
    selector on `data-action-click="like.toggleLike"` in `cuzy-app/clean-theme` (dead selector,
    cosmetic only — see module-search results in the implementing PR).
  - Added `UserImage` (`<user-image>`) to the user module's Vue component set
    (`protected/humhub/modules/user/vue/`, `humhub\modules\user\assets\UserVueAsset`, see
    `docs/develop/ui-js-vuejs-components.md`) — a user's profile image, the Vue analog of
    `user\widgets\Image`. Purely additive; `CommentEntry.vue`'s hand-rolled avatar markup
    (img + online-status overlay) now uses it (`<UserImage v-bind="comment.author" />`),
    depending on `UserVueAsset` the same way it already depends on `LikeVueAsset` for
    `<LikeButton>`.
  - **Removed**: `humhub\modules\like\services\LikeService::generateLikeTitleText()` — it built the
    old title-tooltip text, which the Vue-island like link no longer renders; no known callers.
  - **Removed**: the `humhub\modules\like\widgets\views\likeLink.php` view file —
    `humhub\modules\like\widgets\LikeLink::run()` now returns the `LikeButton` island directly with
    no PHP view in between. Theme overrides of `like/widgets/views/likeLink.php` no longer apply;
    override the `LikeLink` widget or the `LikeButton` Vue component instead. Guests now see the
    like **count** again (non-interactive, no like/unlike controls) — restoring the pre-Vue
    behavior that an earlier iteration of this island had temporarily dropped; guest state
    (login-modal link vs. count) is driven client-side via the `isGuest`/`loginUrl` keys of the
    `user` `registerJsConfig` section (see `docs/develop/ui-js-vuejs.md`). `/like/like/info` is now
    guest-accessible (`guestAllowedActions`); content visibility is still enforced by
    `Content::canView()` in `LikeController::beforeAction()`.
  - Added core `UiModal` (`<ui-modal>`, `protected/humhub/vue/UiModal.vue`) — the first
    native, Vue-owned modal (reuses the legacy `.modal`/`.modal-dialog`/`.modal-backdrop`
    Bootstrap 5 markup/CSS so it looks identical, but owns open/close/backdrop/keyboard/
    focus/scroll-lock itself instead of wrapping `bootstrap.Modal`); see
    `docs/develop/ui-js-vuejs-components.md`. The legacy `modal.confirm()`/`modal.load()`
    bridge to `#globalModal` is unaffected and stays the mechanism for legacy (non-Vue)
    flows. (The comment island's own delete dialogs no longer use it — see the
    REST-convergence entry below.)
  - Added `UserList` (`<user-list>`) to the user module's Vue component set
    (`protected/humhub/modules/user/vue/`, `UserVueAsset`) — a generic paginated user-list
    island (any endpoint returning `{ total, users, hasMore, nextPage }`), and extracted
    `humhub\modules\user\services\UserJsonService::serialize()` (the exact shape
    `CommentJsonService::serializeAuthor()` used to build; that method now delegates here,
    payload unchanged) so both share one implementation.
  - **Replaced**: the like module's user-list modal is now Vue-native. `LikeController::actionUserList()`
    (route `like/like/user-list`) now returns the JSON `{ total, users, hasMore, nextPage }`
    shape instead of rendering `humhub\modules\user\widgets\UserListBox` into the global
    modal — module-search found no external usage of this route (the `UserListBox` widget
    itself is unaffected and still used directly by several external modules, e.g.
    `humhub/mail`, `humhub/polls`, for their own unrelated user lists). `LikeButton.vue`'s
    like-count link now opens a `<UiModal>` containing `<UserList>` instead of delegating to
    `#globalModal`; `LikeVueAsset` gained `CoreVueAsset`/`UserVueAsset` dependencies for
    `<UiModal>`/`<UserList>`. Guest access is unchanged (`user-list` was never in
    `guestAllowedActions`, only `info` was). `LikeService::getUserQuery()`'s ordering gained a
    `like.id DESC` tiebreaker after `like.created_at DESC` — the datetime column's
    one-second resolution otherwise made offset/limit pagination over ties non-deterministic
    (a liker could appear twice, or be skipped, across two "Show more" pages).
  - **Comment module rendered as a Vue island** (`<comment-section>`,
    `humhub\modules\comment\assets\CommentVueAsset`) — comments render entirely client-side
    from JSON now, fed by a new `humhub\modules\comment\services\CommentJsonService` and JSON
    actions on `CommentController`: `list` (window/cursor pagination, replaces the HTML `show`
    action), `create`/`update` (replace `post`/`edit`), `info` (single comment, `showBlocked=1`
    reveal). The server now enforces at most one nesting level on `create` (previously
    JS-only). `humhub\modules\comment\widgets\Comments`'s public API is unchanged
    (`Comments::widget(['content' => $content])`, `parentComment`, `renderOptions`,
    `viewMode`) — the legacy `object` param (`Comments::widget(['object' => $x])`, the API
    before #7917 replaced polymorphic `object` relations with `content_id`/
    `parent_comment_id`) keeps working too, restored as a compatibility mapping onto
    `content`/`parentComment` since module-search found 10 external modules still using it
    (`CommentLink::widget(['object' => $x])` likewise - 2 known callers, also kept working).
  - **Removed**: `humhub\modules\comment\widgets\Comment`, `ShowMore`, `Form`, `EditLink`,
    `CommentControls` (+ their view files, `widgets/views/comments.php`,
    `views/comment/edit.php`) and the HTML branches of `CommentController::actionShow()`
    (only the `mode=popup` branch remains, now rendering the island via `Comments::widget()`)
    / `actionPost()` / `actionEdit()` / `actionLoad()`. `humhub.comment.js` shrinks from a
    full jQuery widget module to a ~90-line bridge: `toggleComment` (same
    `data-action-click="comment.toggleComment"` target in `comment/widgets/views/link.php`,
    now dispatching a `humhub:comment:toggle` CustomEvent on the island's mount element) and
    `scrollActive`/`scrollInactive` (still wired into the comment form's `RichTextField`, see
    `CommentFormShell` below) are all that remain callable from markup; a new
    `humhub:comment:countChanged` listener keeps the wall entry's "Comment (n)" badge
    (rendered by `CommentLink`, unchanged) in sync with the island. Every other export
    (`Comment`, `Form`, `showMore`) and their prototypes are gone. `CommentAsset` (the bridge)
    stays part of `CoreBundleAsset`; the new `CommentVueAsset` (depends on `LikeVueAsset` +
    `CoreApiAsset` - `LikeVueAsset` must register first so `<LikeButton>` resolves inside
    `CommentEntry.vue`) is registered on demand by the widget. `CommentLink`,
    `CommentEntryLinks` are unchanged/kept (`AdminDeleteModal` is removed later in this
    same cycle, see the REST-convergence entry below) -
    islandizing `CommentLink` itself is a documented follow-up, not part of this change.
  - **Breaking for known external modules**, found via module-search while preparing this
    change (recorded here since they were not caught by an earlier "zero external consumers"
    pass that had gated the removal - module owners should audit before upgrading to 1.20):
    `humhub/reportcontent` and the private `cuzy-app/reaction` module hook
    `CommentControls::EVENT_INIT` / `CommentEntryLinks::EVENT_BEFORE_RUN` respectively to
    inject a menu entry/reaction picker into each comment's controls row. Both classes are
    kept (so they still exist/autoload - no fatal error), but neither is instantiated anymore
    (comment entries render from JSON in `CommentEntry.vue`, there is no more per-comment PHP
    widget pass to hook into), so both integrations silently stop firing. The private
    `cuzy-app/external-websites` module's `FirstCommentForm` widget `extends Form` directly -
    this **does** fatal (class no longer exists) and needs a follow-up fix before that module
    can run against 1.20. Separately, the `cuzy-app/saas` theme's override of
    `comment/widgets/views/comments.php` (`require`s the core file) breaks since that view is
    removed - override the `Comments` widget or the new `CommentSection.vue` component
    instead.
  - New reusable **`humhub\widgets\VueFormShell`** mechanism for wrapping deep jQuery form
    widgets inside a Vue island without server-rendering per instance (see
    `docs/develop/ui-js-vuejs-interop.md`, "Form-shell pattern"): the widget owns a bare
    `ActiveForm` shell (`action => '#'`, CSRF disabled, `acknowledge => true`) and a `content`
    closure supplies whatever fields the caller needs, with every element id built from the
    literal token `__VUEFORM__` (via the `VueFormShell::id()` helper); the client
    (`LegacyFormWrapper.vue`) clones the shell per form instance (main form, an open reply form
    per comment, an edit form) by replacing the token with a unique id before mounting. The
    comment form's `humhub\modules\comment\widgets\CommentFormShell` is the reference
    composition (`RichTextField`/`UploadButton` markup) on top of this mechanism; its upload
    field now carries the generic `vueform-upload` class (the former comment-only
    `main_comment_upload` class was dropped - no known theme/module CSS targeted it) that
    `LegacyFormWrapper.vue`'s upload lookup queries.
  - `CommentJsonService`'s serialized comment shape (introduced earlier in this same
    Unreleased cycle, never part of a stable release) replaced the `messageOutput` HTML
    envelope string with raw markdown (`message`) plus an explicitly-typed
    `messageRenderOptions` object — see `RichText::outputMarkdownAndRenderOptions()` and
    `docs/develop/ui-js-vuejs-interop.md`, "RichTextOutput". `RichTextOutput.vue` now takes
    `message`/`render-options` props instead of a single `output` HTML-string prop.
  - **Breaking**: because that path never builds an HTML string to append anything to,
    `AbstractRichText::EVENT_AFTER_RUN` (inherited from `yii\base\Widget::EVENT_AFTER_RUN`) and
    `AbstractRichText::EVENT_AFTER_OUTPUT` never fire for a comment message serialized via
    `RichText::outputMarkdownAndRenderOptions()`/`CommentJsonService` (they still fire normally
    for every other `RichText::output()`/`RichText::widget()` call elsewhere in core, and
    `AbstractRichText::EVENT_BEFORE_OUTPUT` still fires on this path too, since
    `getMarkdown()`'s `onBeforeOutput()` extension pipeline still runs — only the two AFTER
    hooks, whose entire purpose is appending markup to an HTML string, are skipped). This is a
    deliberate, accepted consequence of rendering comments client-side from JSON, not a bug to
    be fixed by re-firing them. Known affected modules: `humhub/legal`'s consent wrapper
    (`onAfterRunRichText`, hooking `EVENT_AFTER_RUN`), `humhub/linkpreview`'s `Viewer` widget
    (hooking `EVENT_AFTER_OUTPUT`), `humhub/translator`'s translate button (hooking
    `EVENT_AFTER_RUN`), and comment-related forks in the private `cuzy-app` modules. A module
    that appended markup to richtext output this way must migrate to the Vue extension
    mechanism instead: `humhub\components\api\SerializeEvent` to contribute payload data per
    comment (surfaced as the serialized comment's `extensions` map), and
    `registerSlotComponent`/`ExtensionSlot` (or a plain registered Vue component reading that
    payload) to render UI from it — see `docs/develop/ui-js-vuejs-interop.md` and
    `docs/develop/ui-js-vuejs-components.md`.
  - **The comment/like islands consume the platform's HTTP API** (`/api/v2`, shipped by core —
    see the API framework entry below and `docs/develop/concept-api.md`) instead of
    core-internal JSON controllers. Consequences in core:
    - **Removed**: the JSON actions of `comment\controllers\CommentController` (`list`,
      `info`, `create`, `update`, `delete` — all introduced earlier in this same Unreleased
      cycle, never released). Only the HTML routes remain (`show` popup mode,
      `perma`).
    - **Removed**: `like\controllers\LikeController` entirely, including the pre-1.19 routes
      `like/like/like`, `like/like/unlike` and the newer `like/like/info`/`like/like/user-list` —
      use `POST /api/v2/like`, `DELETE /api/v2/like`, `GET /api/v2/like/state` and
      `GET /api/v2/like/users` instead. The legacy `like.toggleLike` client had
      already been removed earlier in this cycle (see above), so no core markup calls the
      removed routes anymore.
    - **Removed**: `comment\services\CommentJsonService`, `comment\components\SerializeCommentsEvent`
      and `user\services\UserJsonService` (all unreleased artifacts of this cycle) — serialization
      lives in the owning module's serializer (`comment\serializers\CommentSerializer`,
      `user\serializers\UserSerializer`, `like\serializers\LikeSerializer`,
      `file\serializers\FileSerializer`); the batch extension point is
      `humhub\components\api\SerializeEvent` (same `addData()` accumulator API, plus a `type`
      filter). Blocked-author masking moved fully client-side (the viewer's own block list
      ships via `CoreJsConfig` `user.blockedUserIds`, also readable at
      `GET /api/v2/account/blocked-users`).
    - Added `comment\services\CommentDeleteService` (delete + optional author notification —
      extracted from the controller, shared with the API endpoint) and a `Comment` model
      validation rule enforcing the one-nesting-level constraint on every write path.
    - **Removed**: `comment\widgets\AdminDeleteModal` (+ its view
      `widgets/views/adminDeleteModal.php`), the `comment\models\AdminDeleteCommentForm`
      model and the `comment/comment/get-admin-delete-modal` route. The comment delete
      dialogs — the plain confirm AND the moderator "delete with reason + notify the
      author" mode — are one native Vue modal now
      (`comment\vue\components\CommentDeleteModal.vue`, built on `UiModal` + the
      `HumHubForm` field components), feeding the REST delete endpoint's
      `notify`/`message` parameters. That also drops the view's inline `<script>` (CSP
      nonce) and the jQuery form-scraping off `#globalModalConfirm`, so the comment island
      no longer touches the legacy modal bridge at all. Module-search found no external
      users of any of the three removed symbols; theme overrides of
      `comment/widgets/views/adminDeleteModal.php` no longer apply — override the
      `CommentDeleteModal` Vue component instead. The unrelated
      `content\widgets\AdminDeleteModal` (stream entries) is untouched.
  - **File uploads in Vue forms are native** (`UploadField`,
    `protected/humhub/vue/UploadField.vue`, see
    `docs/develop/ui-js-vuejs-forms.md`), fed by the new endpoints
    `POST /api/v2/file` and `DELETE /api/v2/file/<id>`
    (`humhub\modules\file\controllers\api\FileController`). The legacy
    `file\widgets\Upload*` widgets and `humhub.file.js` are untouched and stay the
    mechanism for server-rendered forms. Details:
    - `comment\widgets\views\commentFormShell.php` no longer renders `UploadButton`,
      `FileHandlerButtonDropdown`, `UploadProgress` or `FilePreview` — the shell is the
      richtext editor plus the (empty) `.richtext-create-buttons` row the island
      teleports its trigger and submit button into. A theme overriding that view must
      drop the upload half accordingly.
    - **Removed** from `humhub\vue\LegacyFormWrapper`: `getFileGuids()`, the upload
      reset inside `clear()`, and the `.vueform-upload` convention class it queried;
      `RichTextField` lost its `getFileGuids()` proxy with it. A shell now carries only
      widgets that have no native counterpart.
    - `file\widgets\FileHandlerButtonDropdown` gained `$itemsOnly`, which renders just
      the handlers' `<li>` entries (for a client-side upload field that owns its own
      trigger and dropdown).
    - `file\serializers\FileSerializer::file()` gained `mimeIcon` (the same icon class
      `FileHelper::getFileInfos()` ships to the legacy widgets). Purely additive.
    - **Accepted break:** a `FileHandlerCollection::TYPE_CREATE` handler that
      programmatically pushes a file into the legacy upload *widget instance* does
      nothing inside a Vue form. Its entries are still rendered (their
      `data-action-click` handlers still fire), core's own upload-by-type entries are
      handled natively, and the documented replacement for "here is an already-uploaded
      file" is a DOM event on the field:
      `element.dispatchEvent(new CustomEvent('humhub:file:attach', {detail: {files: [fileShape]}}))`.
  - **The notification dropdown and the overview page are Vue islands**
    (`NotificationMenu`, `NotificationOverview` in
    `protected/humhub/modules/notification/vue/`, `NotificationVueAsset`), fed by the new
    endpoints `GET /api/v2/notification` and `POST /api/v2/notification/mark-as-seen`
    (`notification\controllers\api\NotificationController`, shapes in
    `notification\serializers\NotificationSerializer`). What a notification class
    contributes is unchanged — the payload carries its own `html()` sentence, and the
    entry markup around it is rendered client-side. Details:
    - **Removed**: the client-side `notification` JS module (`humhub.notification.js`,
      `notification.NotificationDropDown`, `notification.OverviewWidget`,
      `notification.markAsSeen`) and `notification\assets\NotificationAsset` (with its
      entry in `CoreBundleAsset::STATIC_DEPENDS`). Module-search found no external users
      of any of it.
    - **Removed**: `notification\controllers\ListController` (both
      `/notification/list` and `/notification/list/mark-as-seen`),
      `notification\widgets\OverviewWidget` (+ its view),
      `notification\widgets\NotificationFilterForm` (+ its view),
      `notification\models\Notification::loadMore()` and the
      `widgets/views/overview.php` dropdown view. Zero external references to any of
      them; the API endpoints above replace the two routes.
    - `notification\widgets\Overview` (the top-menu widget) now renders the island and
      keeps `#notification_widget.btn-group`; the ids inside it
      (`#icon-notifications`, `#badge-notifications`, `#mark-seen-link`,
      `#dropdown-notifications`, `#notification_overview_list`,
      `#notification_overview_markseen`) are unchanged, so theme CSS and the product tour
      still apply. Theme overrides of the removed views no longer do — override the Vue
      components instead.
    - **Kept as the compatibility surface**: `humhub:notification:updateCount` is still
      triggered on every count change, `humhub:modules:notification:UpdateTitleNotificationCount`
      is still listened to, and the document title still adds
      `humhub.modules.mail.notification.getNewMessageCount()`. `humhub/mail` and its forks
      need no change. New: `humhub:notification:setCount` pushes a count INTO the island —
      that is how `UpdateNotificationCount` (the pjax layout addon) keeps the badge current.
    - `Notification::findGrouped()` gained a `max(notification.id) as group_max_id`
      aggregate and orders by it as the final tiebreaker, so cursor paging cannot return
      overlapping pages (notifications created within the same second were previously
      ordered arbitrarily). Existing callers only see a deterministic order.
    - **Deliberate UI change**: the overview page pages with a "show more" button instead
      of numbered pages (`LinkPager`), for the same reason the endpoint pages by cursor.
    - The web-target renderer (`notification\renderer\WebRenderer`,
      `@notification/views/default.php`, `layouts/web.php`) is untouched and still
      renders a notification server-side for anyone calling it — the platform itself no
      longer does.
    - Added `space\serializers\SpaceSerializer` and `SpaceImage` (`<space-image>`,
      `SpaceVueAsset`) as the space module's first Vue component, used for the space badge
      of an entry. The Vue bridge gained `pageTitle()`, the platform's own per-page title
      state (`ui.view`), which the island needs to prefix the document title without
      accumulating its own count.
  - **The status bar is a Vue island** (`StatusBar`, `protected/humhub/vue/StatusBar.vue`),
    mounted by `humhub\widgets\StatusBar` as `<status-bar id="status-bar">`. The `ui.status`
    JS module keeps its **public API unchanged** — `success`/`info`/`warn`/`error` with the
    same signatures, plus the `humhub:modules:log:setStatus` listener — and forwards to the
    island through the bridge (`humhub.vue.js`'s `status()`/`setStatusHandler()`, which
    queues messages triggered before the island mounts). None of the 18 modules calling
    `require('ui.status')` needs a change. Details:
    - **Removed** from `humhub.ui.status.js`: the `StatusBar` class (and its `module.statusBar`
      instance), `module.template` and `module.initState` — all internals of the jQuery
      implementation, no external users found via module-search.
    - **Messages render as text, not HTML.** The jQuery bar injected them with `.html()`.
      Entities produced by `Html::encode()`/`htmlspecialchars(ENT_QUOTES)` are still decoded
      by the façade, so a caller that pre-escapes its message text stays correct; a caller
      that passes deliberate **markup** (`<strong>…</strong>`) now sees it as text.
    - `humhub\components\View::endBody()` ships the flash message as a JSON string literal
      instead of HTML-encoding it into a JS string. Its former `&quot;` strip — needed to
      keep that literal valid — silently deleted every double quote from a flash message.
    - `_user-feedback.scss` gained the slide transition (`.status-bar-body` hidden by
      default, shown via `.status-bar-visible`) that jQuery used to animate, plus
      `.status-bar-toggle` for the details-toggle cursor. A theme that replaces this block
      (`$prev-user-feedback: true`) keeps working — the bar element only exists while a
      message is shown — but its bar appears and disappears without the slide until it
      adopts those two rules.
  - **The space membership button is a Vue island** (`MembershipButton`,
    `protected/humhub/modules/space/vue/`, `SpaceVueAsset`), fed by the new endpoints
    `GET|POST|DELETE /api/v2/space/<id>/membership`
    (`space\controllers\api\MembershipController`, shape in
    `space\serializers\MembershipSerializer`). One `POST` covers joining, applying and
    accepting an invite, one `DELETE` covers leaving, withdrawing and declining — which
    one it is follows from the current state and the space's join policy, decided server
    side. Both answer the new state, and the island renders every state itself, including
    the request-membership dialog (a native `UiModal` + `HumHubForm`). Details:
    - `space\widgets\MembershipButton` keeps its class, `$space` and
      `EVENT_INIT`/`EVENT_CREATE`, and its presentation is now properties instead of a
      per-button option array: `$buttonClass`, `$pendingClass`, `$memberClass`,
      `$togglerClass`, `$groupClass`, `$showMemberState`, `$reloadOnJoin`. **`$options`,
      `setDefaultOptions()` and `getOptions()` are removed** rather than deprecated — the
      array carried titles, urls and `data-action-*` attributes for markup that no longer
      exists. The properties are nullable, so an `EVENT_INIT` handler supplies a default
      without overriding what the call site configured:

      ```php
      // before
      $membershipButton->setDefaultOptions([
          'requestMembership' => ['attrs' => ['class' => 'btn btn-accent btn-sm']],
          'becomeMember' => ['attrs' => ['class' => 'btn btn-accent btn-sm']],
          'acceptInvite' => ['attrs' => ['class' => 'btn btn-accent btn-sm'], 'togglerClass' => 'btn btn-accent btn-sm'],
          'cancelPendingMembership' => ['attrs' => ['class' => 'btn btn-sm btn-outline-accent']],
      ]);

      // after
      $membershipButton->buttonClass ??= 'btn btn-accent btn-sm';
      $membershipButton->togglerClass ??= 'btn btn-accent btn-sm';
      $membershipButton->pendingClass ??= 'btn btn-sm btn-outline-accent';
      ```

      Known affected: `humhub/enterprise-theme` (`Events::onInitSpaceMembershipButton()`) —
      it sets classes only, so the above is the whole migration. `cuzy-app/category-group`
      hooks `EVENT_CREATE` and subclasses the widget for its own `run()`; it touches
      neither `$options` nor the removed methods and needs no change.
    - **Removed**: `MembershipButton::sanitizeRequestOptions()` and the
      `space/widgets/views/membershipButton.php` view; `space\controllers\MembershipController`
      lost `actionRequestMembershipForm()` (`/space/membership/request-membership-form`)
      with the `views/membership/requestMembership.php` and `requestMembershipSave.php`
      views, `RequestMembershipForm::$options`, and the `requestMembershipSend()` function
      of the `space` JS module. Zero external references to any of them (module-search).
      Theme overrides of those views no longer apply — override the Vue component instead.
    - `MembershipController::getActionResult()` (the web `request-membership`,
      `invite-accept` and `revoke-membership` actions, all kept) always redirects now; an
      AJAX request used to be answered with the re-rendered button. That answer is what
      made the presentation options travel through the client, which #8381 had to harden.
    - The sibling `FollowButton` (still a server-rendered widget) is toggled by the island
      itself, by `data-content-container-id` + `.followButton`/`.unfollowButton` — the
      server no longer sends `data-show-buttons`/`data-hide-buttons`.
    - **A module restricting who may join a space must guard the API controller too.**
      Membership transitions no longer go only through
      `space\controllers\MembershipController`; a guard hooked onto its
      `EVENT_BEFORE_ACTION` (as `cuzy-app/category-group` has) needs the same handler on
      `space\controllers\api\MembershipController`.
  - **The friendship button is a Vue island** (`FriendshipButton`,
    `protected/humhub/modules/friendship/vue/`, `FriendshipVueAsset`), fed by the new
    endpoints `GET|POST|DELETE /api/v2/user/<id>/friendship`
    (`friendship\controllers\api\FriendshipController`, shape in
    `friendship\serializers\FriendshipSerializer`) — the membership button's twin, built the
    same way: one `POST` sends a request or accepts a received one, one `DELETE` withdraws,
    denies or ends, both answer the new state. The endpoints answer `404` while the friendship
    system is disabled, like the web controller. Details:
    - `friendship\widgets\FriendshipButton` keeps its class, `$user`, `EVENT_INIT` and
      `isVisibleForUser()`, and gained the presentation properties `$buttonClass`,
      `$stateClass`, `$togglerClass`, `$groupClass`. **`$options`, `setDefaultOptions()`,
      `getOptions()` and `sanitizeRequestOptions()` are removed** (with the
      `widgets/views/friendshipButton.php` view and the request-options unit test) — the array
      carried titles, urls and `data-action-*` attributes for markup that no longer exists.
      Like the membership button the properties are nullable, so an `EVENT_INIT` handler can
      set a default with `??=` without overriding the call site. The option array allowed a
      separate class per button, the properties give the three state buttons (pending,
      received request, friends) one look — which is what both core call sites always did.
    - `RequestController::getActionResult()` (the web `request/add` and `request/delete`
      actions, both kept) always redirects now.
    - **Removed**: the `relationship` action of the `content.container` JS module
      (`data-action-click="content.container.relationship"`) together with its
      `data-button-options` posting and the `data-show-buttons`/`data-hide-buttons`
      convention. It existed for the membership and friendship buttons only, both of which
      are islands now; module-search found no external users of any of the three attributes.
      `content.container.follow`/`unfollow` are untouched.
  - **The activity box is a Vue island** (`ActivityBox`,
    `protected/humhub/modules/activity/vue/`, `ActivityVueAsset`), fed by the new endpoint
    `GET /api/v2/activity` (`activity\controllers\api\ActivityController`, shape in
    `activity\serializers\ActivitySerializer`). Unlike the buttons the island is the whole
    panel, not just its list: `activity\widgets\ActivityBox` renders the mount point with the
    first page inlined, the container it is scoped to and the rendered `PanelMenu` (whose
    entries modules keep contributing server-side). What an activity class contributes is
    unchanged — the payload carries its own `asWeb()` sentence, and the entry markup around it
    is rendered client-side. `#panel-activities`, `#activity-box-content.activities`,
    `div.activity-entry[data-activity-id]` and `.activity-box-entry` are unchanged, so theme
    CSS still applies. Details:
    - **Removed**: the client-side `activity` JS module (`humhub.activity.js`,
      `activity.ActivityBox`) and `activity\assets\ActivityAsset` (with its entry in
      `CoreBundleAsset::STATIC_DEPENDS`). The custom scrollbar it installed (`niceScroll`) is
      gone with it; the box scrolls natively, as its CSS always said.
    - **Removed**: `activity\controllers\ActivityBoxController` (the `/activity/activity-box/load`
      route), `activity\widgets\ActivityBox::renderActivity()`,
      `activity\services\RenderService::getWeb()` and the two view files
      `activity/widgets/views/activity-box.php` and `activity/views/layouts/web.php`. The
      controller's static `getQuery()` moved to `activity\services\ActivityWindowService::query()`,
      which the API and the widget share. `RenderService`'s mail representations are untouched.
      Module-search found no external users of any of it — a module rendering activities in mail
      or dispatching them through `ActivityManager` is unaffected.
    - New: `activity\live\NewActivity`, sent by `ActivityManager::dispatch()` after the
      grouping ran, for containers only (the live system routes by container and visibility).
      It carries no grouping detail: a consumer reacts by reading the list again.
  - **The space menu is a Vue island** (`SpaceChooser` and the small `SpaceChooserToggle`,
    `protected/humhub/modules/space/vue/`), fed by the new general space endpoints
    `GET /api/v2/space` and `GET /api/v2/space/states`
    (`space\controllers\api\SpaceController`, shapes in `space\serializers\SpaceSerializer`).
    The `<li>`, the button anchor and the ids theme CSS uses (`#space-menu`,
    `#space-menu-dropdown`, `#space-menu-spaces`, `#space-menu-search`) are unchanged, as is the
    markup of an entry (`[data-space-chooser-item]`, `data-space-guid`, the four relation
    attributes). What the menu shows is loaded when it is first opened, as before. Details:
    - **Removed**: the client-side `space.chooser` JS module (`humhub.space.chooser.js`, with the
      `niceScroll` scrollbar it installed) and `space\assets\SpaceChooserAsset` (with its entry
      in `CoreBundleAsset::STATIC_DEPENDS`). A module overriding functions of that JS module —
      `cuzy-app/classified-space` does — has to move to the island or ship its own.
    - **Removed** from `space\widgets\Chooser`: `$lazyLoad`, `$viewName`, `renderItems()`,
      `attachItem()`, `getLazyLoadResult()`, `getMemberships()`, `getFollowSpaces()`,
      `getViewParams()`, `getJsConfigParams()`, `configure()`, `canRun()` and
      `getNoSpaceHtml()`, together with the `widgets/views/spaceChooser.php` view. A theme
      subclassing the widget to render its own menu — `humhub/enterprise-theme` does — no longer
      has those hooks: the markup is the island's. Modules that need their own data on a space
      can attach it through the API's `SerializeEvent` (`extensions`).
    - **Removed**: `space\controllers\BrowseController::actionSearchLazy()` (the route
      `/space/browse/search-lazy`), which existed only to render the menu's list. `search-json`
      is untouched, including its `target=chooser` mode, and so are
      `space\widgets\SpaceChooserItem` and `Chooser::getSpaceResult()` — the space picker and
      modules (`humhub/sharebetween`, `cuzy-app/cloner`, `cuzy-app/group-advanced`) use them.
    - Following or unfollowing a space now triggers `humhub:space:followed` /
      `humhub:space:unfollowed` (`humhub.content.container.js`) instead of calling into the
      space menu's widget. Anything that kept its own copy of the menu list can listen for them.
  - **Attached files are a Vue island** (`AttachedFiles`, `protected/humhub/modules/file/vue/`,
    `FileVueAsset`). `file\widgets\ShowFiles` keeps its class, `$object`, `$active` and
    `$preview` and is now only the mount point: it serializes the record's stream files with
    `file\serializers\FileSerializer` and hands them over as props. The six modules calling
    `ShowFiles::widget(['object' => $record])` (`humhub/mail`, `cuzy-app/mail`,
    `cuzy-app/events-manager`, `cuzy-app/post-to-wiki`, `cuzy-app/survey`,
    `cuzy-app/survey-advanced`) need no change. `.hideOnEdit`, `.post-files`,
    `.post-files-audio|-videos|-images`, `.col-media`, `.well.post-file-list`, `ul.files` and
    `li.file-preview-item.mime.<mimeIcon>` are unchanged, so theme CSS still applies; the
    comment island renders its attachments with the same component now, which is what the
    change is really for — the two used to be separate implementations of the same visual.
    Details:
    - **Removed**: the view `file/widgets/views/showFiles.php`. A theme overriding it has to
      move to the component (or render its own island); module-search found no override.
    - **Removed**: `humhub\widgets\JPlayerPlaylistWidget` with its `jPlayerAudio.php` view,
      `humhub\assets\JplayerAsset`, `humhub\assets\JplayerModuleAsset`, the `media.Jplayer` JS
      module (with its entry in `CoreExtensionAsset`) and the `npm-asset/jplayer` +
      `xj/yii2-jplayer-widget` composer dependencies. `ShowFiles` was the only user of any of
      it (module-search: no external ones) — **attached audio now plays in native, individually
      labelled `<audio controls>` players instead of a jPlayer playlist**, and the `.jp-*`
      theme rules in `_file.scss`/`_comment.scss` are gone.
    - **Fixed**: the `excludeMediaFilesPreview` setting of the file module ("Exclude media
      files from stream attachment list", on by default for new installations) works again.
      The `file.Preview` JsWidget only added a `hiddenFile` class to those entries, and no rule
      for it has existed since the Bootstrap 5 migration, so media files stayed in the list
      below the preview grid. They are now really left out.
    - The `file.Preview` JsWidget itself is untouched and still renders the file list of the
      upload/edit paths (`wallCreateContentFormFooter.php`, `post/edit.php`,
      `file\widgets\Upload`, `file\widgets\ActiveFileUpload`), where it doubles as the live
      preview of an in-progress upload.
    - Smaller deviations: attachment sizes are formatted client-side rather than through
      `Yii::$app->formatter->asShortSize()`, and the preview grid no longer carries a
      `post-files-<uniqueId>` id (nothing referenced it).
    - New in the API's file shape: **`downloadUrl`**, the same file with the response forcing a
      download. Purely additive. The two hints `ShowFiles` resolves per file — `viewUrl` (the
      file has a viewer beyond plain download) and `highlight` (a search hit) — are deliberately
      **not** part of it: both are caller specific, and the comment payload is cached
      caller-neutrally (see `docs/develop/concept-api.md`).
    - New helpers: `file\libs\FileHelper::getViewUrl()` (extracted from `createLink()`, whose
      output is unchanged) and `file\libs\FileHelper::isSearchHighlighted()` (moved from the
      protected `FilePreview::isHighlighed()`, which is removed).
- Added the **HTTP API framework** in `humhub\components\api\` and the first core endpoints
  under `/api/v2` — see `docs/develop/concept-api.md`. Purely additive for existing modules;
  the `humhub/rest` module and the 13 modules extending its `BaseController` keep working
  unchanged.
  - `humhub\components\api\BaseController` is the base class of a core API controller
    (`humhub\modules\<module>\controllers\api\`), `ApiRules::v2()` prefixes the rules a module
    declares in its `config.php` `urlManagerRules`, `Format` holds the v2 value conventions
    (ISO-8601 UTC timestamps, camelCase attribute names) and `SerializeEvent` is the batch
    serializer extension point. Serializers live in `humhub\modules\<module>\serializers\`.
  - Core endpoints in this release: comment window/CRUD (`comment`), like state/toggle/users
    (`like`), the caller's account and block list (`user`), file upload/delete (`file`),
    the notification window (`notification`), the caller's space membership (`space`) and the
    caller's friendship with a user (`friendship`).
  - A module may contribute authentication methods to *every* API controller by handling
    `BaseController::EVENT_COLLECT_AUTH_METHODS` (`AuthMethodsEvent`) — how the rest module
    ≥ 0.13 makes token authentication apply to core endpoints. Browser-session authentication
    ships in core (`humhub\components\api\SessionAuth`) and is opt-in **per controller**
    (`BaseController::$enableSessionAuth`, default `false`), so a module's own token-oriented
    endpoints never become reachable from a browser session.
  - Added `humhub\components\Request::$isSessionAuthenticated`, set by `SessionAuth`.
    `humhub\components\gates\GateFilter` no longer infers `RequestClass::Api` from
    `Yii::$app->user->enableSession` alone, and `humhub\modules\user\components\Impersonation::isActive()`
    no longer short-circuits on it: a session-authenticated API request passes the normal user
    gates (2FA, legal, onboarding) and keeps the impersonation restrictions. A module that
    implements its own API-style authentication should set the flag when it authenticates
    someone by their browser session.
  - **The API payloads are caller-neutral**: nothing in a comment or user shape depends on who
    is asking, so one serialization serves every reader (and can be cached). Consequences for
    a module reading these shapes or attaching to them:
    - `comment\serializers\CommentSerializer` no longer emits `canEdit`, `canDelete` or
      `likes`; `user\serializers\UserSerializer::short()` no longer emits `online` (nor the
      localized `imageAlt`, which `user/vue/UserImage.vue` builds itself). The replacements are
      `GET /api/v2/comment/<id>/permissions` (fetched when an entry's context menu opens) and
      `GET /api/v2/like/states?recordIds=…` (one batched request per window, `{recordId:
      {total, liked, canLike}}`).
    - Data attached via `humhub\components\api\SerializeEvent` must be **caller-neutral**
      too — caller-specific module state belongs in that module's own endpoint, see
      `docs/develop/ui-js-vuejs-extensions.md`.
    - Added `like\serializers\LikeSerializer::statesForRecords()`/`statesByRecordId()`,
      `like\services\LikeService::countsForRecords()`/`likedRecordIds()`/`preloadState()`
      and `humhub\models\RecordMap::getByIds()` — batched building blocks for the above,
      reusable wherever many records are serialized at once (stream entries next).
    - `humhub\vue\DropdownMenu` gained an `open` event (fired from Bootstrap's
      `show.bs.dropdown`) and a `loading` prop, so a menu can load its content on demand.
    - Because the payloads are caller-neutral they are now **cached server-side**:
      `comment\services\CommentPayloadCache` wraps the serializer and is what the comment
      widget and the API read. A comment create/edit/delete retires its content's entries
      immediately (`Comment::afterSave()`/`afterDelete()`); the new `comment` module property
      `payloadCacheTtl` (default `3600`, `0` disables) only bounds how long a payload may lag
      behind data it embeds without owning — the author's display name and profile image, and
      whatever a module attached through `SerializeEvent`. A module whose `SerializeEvent`
      data can change independently of the comment should keep that in mind (or attach it
      client-side instead).
- **Breaking**: `humhub\modules\content\widgets\richtext\extensions\RichTextExtension` gained a
  new interface method, `getRenderOptions(): array`, needed for
  `ProsemirrorRichText::getMarkdownAndRenderOptions()` (the client-render counterpart of
  `RichText::output()` backing the comment payload change above) to let an extension
  contribute per-record data — e.g. `OembedExtension`'s server-fetched preview HTML — that a
  client cannot derive from the processed markdown text alone. A custom `RichTextExtension`
  implementation registered via `AbstractRichText::addExtension()` must add this method
  (return `[]` if it has nothing to contribute — true for the overwhelming majority of
  extensions, whose entire contribution already lives in the markdown text their
  `onBeforeOutput()` returns). Module-search found zero external implementers of this
  interface as of this writing; extending `RichTextContentExtension`/`RichTextLinkExtension`
  (the base classes every core extension uses) already provides the new method as a no-op
  default, so most custom extensions need no change at all.
- Added `humhub\modules\content\models\Content::EVENT_BEFORE_HARD_DELETE` (`ContentEvent`),
  triggered from `Content::hardDeleteInternal()` right before a `Content` record is physically
  removed. Modules that store rows referencing `content_id` with a restrictive (non-cascading)
  foreign key — like Comment and Like — must clean those rows up on this event, **in addition**
  to `ContentActiveRecord::EVENT_BEFORE_DELETE`. The latter only fires while deleting the
  polymorphic content object itself (e.g. a `Post`); when a `Content` record is hard-deleted
  directly because its polymorphic object no longer exists (e.g. by `IntegrityController`),
  `ContentActiveRecord::EVENT_BEFORE_DELETE` never fires and addon rows were left behind,
  causing foreign key constraint violations during `integrity/run`.
- Added the central **user gate** system for modules that intercept requests to route the user
  through a mandatory flow (2FA check, terms acceptance, first-use wizards) — see
  `docs/develop/user-gates.md`. Modules using `Controller::EVENT_BEFORE_ACTION` for this purpose
  should migrate to a `UserGate` registered via `GateManager::EVENT_INIT_GATES` and bump
  `humhub.minVersion` to `1.19`. Until migrated, legacy interceptors keep working but should
  guard against double interception with
  `if (!$event->isValid || Yii::$app->response->getIsRedirection()) { return; }`.
  - **Removed** (unused by any known module): `ControllerAccess::RULE_MUST_CHANGE_PASSWORD` and
    `ControllerAccess::RULE_MAINTENANCE_MODE` (incl. their validators
    `validateMustChangePassword()` / `validateMaintenanceMode()`),
    `AccessControl::forceChangePassword()` / `checkMaintenanceMode()`, and the `codeCallback`
    mechanism on `ControllerAccess`/`DelegateAccessValidator`/`AccessControl`. A custom
    `ControllerAccess`/validator that still declares and sets a `codeCallback` now fails with an
    `InvalidConfigException` instead of the callback being silently ignored. Enforcement moved to
    `humhub\modules\user\components\MustChangePasswordGate` and
    `humhub\modules\user\components\MaintenanceModeGate`; the forced logout of non-admins
    during maintenance now happens in the gate's `onIntercept()` hook.
  - Requests classified as AJAX/PJAX now receive `401` + JSON `{gate, url}` (plus an
    `X-Redirect` header handled by `yii.js`) instead of a `302` to an HTML page when a gate
    intercepts; token-authenticated API requests (server-side, `enableSession = false`)
    receive `403` + JSON.
  - **Removed** `Controller::$doNotInterceptActionIds` and `Controller::isNotInterceptedAction()`
    (`@since 1.9`, unused by the gate system and uncalled by any known module). The flag was a
    controller's way to opt individual actions out of interception; that is now handled by the
    gates themselves (`UserGateInterface::appliesTo()` / `getAllowedRoutes()`), and stateless
    API endpoints are exempt via the request classification. A module controller that still
    declares the property keeps working (it becomes an unused own property) but the declaration
    can be removed.
- `humhub\modules\content\components\ActiveQueryContent::readable()` and `::userRelated()` no
  longer accept a `$user` parameter. The user is now resolved once, in the constructor — either
  the current session user (`Yii::$app->user->getIdentity()`) or an explicit user passed as the
  second constructor argument: `new ActiveQueryContent($modelClass, $user)`. Modules calling
  `->readable($user)` or `->userRelated($scopes, $user)` must switch to constructing the query
  with that user instead.
  **Warning:** PHP silently ignores the extra argument, so an unmigrated call does not fail —
  it evaluates access for the **session** user instead of the passed one: over-broad results
  for admins, empty results in console/queue jobs. Audit every `readable(...)`/`userRelated(...)`
  call that passes a user.
- Icon-only `Button` widgets (no visible label, only an icon) now automatically set `aria-label` from
  the tooltip text. Module developers should always call `->tooltip('...')` on icon-only buttons —
  omitting it logs a `Yii::warning()` in `YII_DEBUG` mode.
  Explicit `aria-label` via `->options(['aria-label' => '...'])` always takes precedence.
- Split rich text short converters by output safety
  - Added `humhub\modules\content\widgets\richtext\converter\RichTextToShortHtmlConverter`
    producing HTML encoded short previews (with the `nl2br` option). Use this whenever
    the result will be rendered inside an HTML view.
  - `humhub\modules\content\widgets\richtext\converter\RichTextToShortTextConverter`
    now returns **unencoded** plain text and no longer supports the `nl2br` option.
    Use this for plain text contexts such as mail subjects.
    **Modules rendering its result in HTML views must switch to
    `RichTextToShortHtmlConverter` or wrap the output with `Html::encode()`** to
    avoid XSS. Direct callers of `convertToShortText()` are affected in the same
    way — use the new `convertToShortHtml()` for HTML rendering.
  - Added `convertToShortHtml()` on `AbstractRichTextConverter` and corresponding
    `RichText::FORMAT_SHORT_HTML` / `RichText::FORMAT_SHORT_TEXT` format constants.
    `RichText::FORMAT_SHORTTEXT` is deprecated and aliased to `FORMAT_SHORT_HTML`
    for backward compatibility. `RichText::preview()` internally uses `FORMAT_SHORT_HTML`.
  - `SocialActivity::getContentPreview()` / `ContentHelper::getContentPreview()` now
    use `RichTextToShortHtmlConverter` to keep their previous (HTML encoded) output.
  - Mail subjects are now decoded centrally in
    `humhub\modules\notification\targets\MailTarget::handle()` and in
    `BaseNotification::asArray()`. Per-notification `Html::decode()` workarounds in
    `getMailSubject()` overrides should be removed.
- Refactored `ContentAddons`
  - Improved `humhub\modules\content\components\ContentAddonActiveRecord`, now required `content_id` attribute
    - Removed `user` relation, use `createdBy` instead.
  - Removed `humhub\modules\content\components\ContentAddonController`
  - Introduced `ContentProvider` interface (May change!)
- Added `RecordMap` to improve polymorphic models relations
- Refactored `comment` module
  - Replaced Polymorphic Relations with `comment.content_id` and `comment.parent_comment_id`
  - Introduced `CommentListService`
  - Removed `CommentForm`
  - `Comment::getUrl()` default for `$scheme` changed from `true` to `false` (aligned with `ContentAddonActiveRecord::getUrl()`) — pass `getUrl(true)` where an absolute URL is required
- Refactored `like` module
  - Introduced `LikeService` and added `like.content_id`
  - Used `RecordMap` for ContentAddon relations
- Removed methods `getContentPlainTextInfo()` and `getContentPlainTextPreview()` from the class `SocialActivity`(`BaseNotification`)
  - Replace them with `getContentInfo()` and `getContentPreview()` in all extended classes, especially inside the method `getMailSubject()`
- Replaced classes:
  - `humhub\widgets\BaseMenu` => `humhub\modules\ui\menu\widgets\Menu`
  - `humhub\widgets\Button` => `humhub\widgets\bootstrap\Button`
  - `humhub\widgets\Label` => `humhub\widgets\bootstrap\Badge`
  - `humhub\widgets\ContentTagDropDown` => `humhub\modules\content\widgets\ContentTagDropDown`
  - `humhub\widgets\DatePicker` => `humhub\modules\ui\form\widgets\DatePicker`
  - `humhub\widgets\DurationPicker` => `humhub\modules\ui\form\widgets\DurationPicker`
  - `humhub\widgets\GlobalConfirmModal` => `humhub\widgets\modal\GlobalConfirmModal`
  - `humhub\widgets\GlobalModal` => `humhub\widgets\modal\GlobalModal`
  - `humhub\widgets\Link` => `humhub\widgets\bootstrap\Link`
  - `humhub\widgets\LinkPager` => `humhub\widgets\bootstrap\LinkPager`
  - `humhub\widgets\Modal` => `humhub\widgets\modal\Modal`
  - `humhub\widgets\ModalButton` => `humhub\widgets\modal\ModalButton`
  - `humhub\widgets\ModalClose` => `humhub\widgets\modal\ModalClose`
  - `humhub\widgets\ModalDialog` => `humhub\widgets\modal\Modal`
  - `humhub\widgets\Tabs` => `humhub\widgets\bootstrap\Tabs`
  - `humhub\widgets\TimePicker` => `humhub\modules\ui\form\widgets\TimePicker`
  - `humhub\modules\search\interfaces\Searchable` => `humhub\modules\content\interfaces\Searchable`
  - `humhub\components\queue` => `humhub\modules\queue\ActiveJob`
  - `humhub\libs\Html` => `humhub\helpers\Html`
  - `humhub\libs\ThemeHelper` => `humhub\helpers\ThemeHelper`
  - `humhub\modules\activity\widgets\Stream` => `humhub\modules\activity\widgets\ActivityStreamViewer`
  - `humhub\modules\content\components\actions\ContentContainerStream` => `humhub\modules\stream\actions\ContentContainerStream`
  - `humhub\modules\content\widgets\WallEntry` => `humhub\modules\content\widgets\stream\WallStreamEntryWidget`
  - `humhub\modules\space\modules\manage\widgets\Menu` => `humhub\modules\space\widgets\HeaderControlsMenu`
  - `humhub\modules\topic\widgets\TopicLabel` => `humhub\modules\topic\widgets\TopicBadge`
  - `humhub\modules\ui\form\widgets\ActiveField` => `humhub\widgets\form\ActiveField`
  - `humhub\modules\ui\form\widgets\ActiveForm` => `humhub\widgets\form\ActiveForm`
  - `humhub\modules\ui\form\widgets\ContentHiddenCheckbox` => `humhub\widgets\form\ContentHiddenCheckbox`
  - `humhub\modules\ui\form\widgets\ContentVisibilitySelect` => `humhub\widgets\form\ContentVisibilitySelect`
  - `humhub\modules\ui\form\widgets\FormTabs` => `humhub\widgets\bootstrap\FormTabs`
  - `humhub\modules\ui\form\widgets\SortOrderField` => `humhub\widgets\form\SortOrderField`
  - `humhub\modules\ui\mail\DefaultMailStyle` => `humhub\helpers\MailStyleHelper`
  - `humhub\modules\ui\view\components\View` => `humhub\components\View`
  - `humhub\modules\ui\view\helpers` => `humhub\helpers\ThemeHelper`
  - `humhub\modules\user\components\ProfileStream` => `humhub\modules\user\actions\ProfileStreamAction`
  - `humhub\modules\user\widgets\UserPicker` => `humhub\modules\user\widgets\UserPickerField` for rendering, `humhub\modules\user\models\UserPicker` for searching
- Removed classes:
  - `humhub\widgets\BootstrapComponent`
  - `humhub\assets\Select2BootstrapAsset`
  - `humhub\modules\search\events\SearchAddEvent`
  - `humhub\libs\DynamicConfig`
  - `humhub\modules\content\widgets\LegacyWallEntryControlLink`
  - `humhub\modules\content\widgets\Stream`
  - `humhub\modules\directory\Module`
  - `humhub\modules\file\widgets\FileUploadButton`
  - `humhub\modules\file\widgets\FileUploadList`
  - `humhub\modules\queue\driver\MySQLCommand`
  - `humhub\modules\user\authclient\AuthClientHelpers`
  - `humhub\modules\user\authclient\Facebook`
  - `humhub\modules\user\authclient\GitHub`
  - `humhub\modules\user\authclient\Google`
  - `humhub\modules\user\authclient\LinkedIn`
  - `humhub\modules\user\authclient\Live`
  - `humhub\modules\user\authclient\Twitter`
  - `humhub\modules\user\authclient\ZendLdapClient`
- Replaced methods:
  - `humhub\widgets\bootstrap\Link::asLink()` => `humhub\widgets\bootstrap\Link::to()`
  - `humhub\widgets\bootstrap\Button::asLink()` => `humhub\widgets\bootstrap\Link::to()`
  - `humhub\widgets\bootstrap\ModalButton::asLink()` => `humhub\widgets\bootstrap\Link::modal()`(new since v1.19)
  - `humhub\modules\ui\menu\widgets\Menu->addItem([...])` => `humhub\modules\ui\menu\widgets\Menu->addEntry(new MenuLink([...]))`(used in module files `Events.php` as `$event->sender->addItem([...])`)
  - `humhub\widgets\bootstrap\Link::userPickerSelfSelect()` => `humhub\modules\user\widgets\UserPickerField::selfSelect()` or use new option `UserPickerField->selfSelect`
  - `humhub\modules\content\models\Content->delete()` => `humhub\modules\content\models\Content->getPolymorphicRelation()->delete()`
  - `humhub\modules\content\models\Content->hardDelete()` => `humhub\modules\content\models\Content->getPolymorphicRelation()->hardDelete()`
  - `humhub\modules\notification\components\BaseNotification->getAsText()` => `text()` — the shims were deprecated since 1.2 and are now removed
  - `humhub\modules\notification\components\BaseNotification->getAsHtml()` => `html()`
  - `humhub\modules\user\models\User->canViewAllContent()` => `humhub\modules\user\models\User->canManageAllContent()` — now permission-based: requires the new `ManageAllContent` permission and the admin module setting `enableManageAllContentPermission`; the semantics changed from viewing to managing all content
  - `humhub\modules\user\models\User->canApproveUsers()` => `humhub\modules\user\models\User->canManageUsers()`
  - `humhub\modules\space\models\Space->isModuleEnabled($id)` => `$container->moduleManager->isEnabled($id)` — was deprecated; the module manager is available on any `ContentContainerActiveRecord`
- Refactored `Activities`
  - Make sure Content related Activities are now extended from `BaseContentActivity`
  - `getTitle` and `getDescription` are now `static`.
  - Instead of View files you need to implement a `getMessage()` method which returns the Activity text.
  - Use following code to create a Activity `ActivityManager::dispatch(TaskCompletedActivity::class, $this->task, $user)`
  - Database: the `activity` table's polymorphic `object_model`/`object_id` columns were replaced by `contentcontainer_id`, `content_id` and `content_addon_record_id` foreign keys — modules querying the table directly must migrate.
  - **Upgrade note:** the activity restructure migration (`m260108_115053_new_structure`) is irreversible (no `down()`), rewrites the whole table with multi-table UPDATEs and permanently deletes activities that cannot be resolved to existing content (logged). Take a database backup before upgrading and plan a maintenance window on installations with a large `activity` table.
- Introduced **UserSource architecture** — separates user provisioning (who owns the user) from authentication (how the user logs in)
  - New `humhub\modules\user\source\UserSourceInterface` — contract for user provisioning sources
  - New `humhub\modules\user\source\BaseUserSource` — abstract base with sensible defaults; provides a default `updateUser()` that applies attributes listed in `$managedAttributes`
  - New `humhub\modules\user\source\LocalUserSource` — handles self-registered / admin-created users; default fallback for any AuthClient not claimed by another UserSource
  - New `humhub\modules\user\source\GenericUserSource` — fully config-driven source for custom integrations
  - New `humhub\modules\user\source\UserSourceCollection` — application component (`Yii::$app->userSourceCollection`); exposes `findUserSourceForAuthClient(string $clientId)` for AuthClient → UserSource dispatch
  - New `humhub\modules\user\services\UserSourceService` — per-user capability checks and lifecycle helpers
    - `UserSourceService::getForUser(?User $user = null)` — factory method; falls back to current identity
    - `UserSourceService::updateUser(array $attributes)` — updates user via UserSource and fires lifecycle event
    - `UserSourceService::deleteUser()` — removes user via UserSource and fires lifecycle event
    - `UserSourceService::triggerAfterCreate(User $user)` — fires lifecycle event after creation
  - New `humhub\modules\ldap\source\LdapUserSource` — extracted from `LdapAuth`; handles LDAP user lifecycle
  - Database: `user.auth_mode` + `user.authclient_id` replaced by `user.user_source` (string source ID)
  - Database: LDAP identity now stored in `user_auth` table (`source='ldap'`, `source_id=<idAttribute value>`) — consistent with OAuth
  - **Upgrade note — external auth identities:** the migration only moves `auth_mode = 'ldap'` into `user_auth`. For every other value (e.g. `saml`, `jwt`, spd-login) the `auth_mode`/`authclient_id` columns are dropped **without** a data move — affected users end up as `user_source = 'local'` and lose their external identity mapping. Until the SSO modules ship their own pre-drop migrations, update those modules first (once available) or back up the `user.auth_mode` / `user.authclient_id` columns before upgrading so the identities can be restored into `user_auth` afterwards.
  - New class-level lifecycle events on `UserSourceService` — listen via `Event::on(UserSourceService::class, ...)`:
    - `UserSourceService::EVENT_AFTER_CREATE` (`'afterUserSourceCreate'`) — fired after a UserSource creates a user
    - `UserSourceService::EVENT_AFTER_UPDATE` (`'afterUserSourceUpdate'`) — fired after a UserSource updates a user
    - `UserSourceService::EVENT_AFTER_DELETE` (`'afterUserSourceDelete'`) — fired after a UserSource removes a user
  - **AuthClient → UserSource link is declarative on the UserSource side** — AuthClients stay vanilla `\yii\authclient\*` implementations with no HumHub-specific interfaces. Each UserSource declares which AuthClient IDs it is responsible for via `$allowedAuthClientIds`; that list governs login authorisation, sync, and createUser dispatch.
  - LDAP: `LdapUserSource::$allowedAuthClientIds` (default `['ldap']`) — restricts which auth clients LDAP users may use; configurable in admin UI
  - LDAP: login with a disallowed auth client is now blocked in `AuthController` with an error flash
- Removed `humhub\modules\user\authclient\BaseClient`
  - Replace `extends BaseClient` with `extends \yii\authclient\BaseClient` (or `BaseFormClient` for form-based clients)
  - `BaseClient::EVENT_CREATE_USER` removed — replace listeners with `Event::on(UserSourceService::class, UserSourceService::EVENT_AFTER_CREATE, $handler)`
  - `BaseClient::EVENT_UPDATE_USER` removed — replace listeners with `Event::on(UserSourceService::class, UserSourceService::EVENT_AFTER_UPDATE, $handler)`
  - `BaseClient::canBypassApproval()` removed — configure on the UserSource via `$approval` / `$trustedAuthClientIds` (see below)
  - `BaseClient::beforeSerialize()` removed — implement `humhub\modules\user\authclient\interfaces\SerializableAuthClient` instead
- Removed `humhub\modules\user\authclient\interfaces\SyncAttributes` (was deprecated since 1.16; the legacy sync path in `AuthClientService` is removed)
  - Replacement: have a UserSource declare which AuthClient IDs it accepts via `$allowedAuthClientIds`. The UserSource's `updateUser()` (or the default in `BaseUserSource` driven by `$managedAttributes`) writes the synced fields.
  - Affected modules requiring migration: `saml-sso`, `jwt-sso`, `spd-login`, and any custom AuthClient that implemented `SyncAttributes`. Existing local users authenticating via SAML/JWT will no longer be sync'd unless an admin opts in by adding the auth client ID to `LocalUserSource::$allowedAuthClientIds` and listing the synced fields in `LocalUserSource::$managedAttributes`.
- Removed `humhub\modules\user\authclient\interfaces\SerializableAuthClient` and its `beforeSerialize()` hook. AuthClient instances are no longer stored in the session at all — `AuthController` now hands the auth state to the registration form via `humhub\modules\user\services\PendingAuthService`, which captures only the client id + already-normalised user attributes. The AuthClient is reconstructed from the AuthClientCollection on the receiving side. Closures in normalize maps, connection handles, and other non-serialisable client state are no longer a session concern. Modules with custom AuthClients can drop `implements SerializableAuthClient` and their `beforeSerialize()` method.
- Deprecated `humhub\modules\user\authclient\interfaces\ApprovalBypass` — approval is now configured on the UserSource side. AuthClients stay vanilla `\yii\authclient\*` implementations.
  - The interface is kept as an empty marker so modules still implementing it don't fatal-error, but core no longer reads it. `RegistrationController` and `AuthClientService::allowSelfRegistration()` no longer fall back on `instanceof ApprovalBypass`.
  - Migration: drop `implements ApprovalBypass` from your AuthClient. To skip approval for users provisioned via that auth client, configure the responsible UserSource with `'approval' => true, 'trustedAuthClientIds' => ['<client-id>']` — or leave `$approval = false` (default) to skip approval entirely for that source.
  - `UserSourceInterface::requiresApproval(?string $authClientId = null)` decides per-request: form-based self-registration passes `null`; auth-client-driven registration passes the client ID.
  - Core `LdapAuth` no longer implements `ApprovalBypass`; the LDAP approval policy is owned by `LdapUserSource`.
- Deprecated `humhub\modules\user\authclient\interfaces\PrimaryClient` is no longer read by core — `AccountController::actionConnectedAccounts()` and `AccountSettingsMenu::getSecondaryAuthProviders()` filter purely on `BaseFormClient` now (source-owning clients all extend it). `Password` no longer implements `PrimaryClient`. Interface kept as empty marker for backwards compatibility.
- Deprecated `humhub\modules\user\models\User::STATUS_DISABLED` — renamed to `STATUS_DEACTIVATED` for clarity (the constant describes an admin-deactivated account, not a disability). The deprecated alias points to the same value (`0`), so persisted statuses and DB queries are unaffected; modules should switch references to `User::STATUS_DEACTIVATED`. The associated i18n key in `AdminModule.user` was also renamed `'Disabled'` → `'Deactivated'` (and `'Disabled users'` → `'Deactivated users'`); existing language files have been migrated.
- Removed `humhub\modules\user\authclient\interfaces\StandaloneAuthClient` and its dispatcher fallback. Migrate to the new `humhub\modules\user\authclient\interfaces\CustomAuth` interface:
  ```php
  // Before:
  use humhub\modules\user\authclient\BaseClient;
  use humhub\modules\user\authclient\interfaces\StandaloneAuthClient;

  class MyClient extends BaseClient implements StandaloneAuthClient {
      public function authAction($authAction) {
          // custom logic
          return $authAction->authSuccess($this);
      }
  }

  // After:
  use humhub\modules\user\authclient\interfaces\CustomAuth;
  use yii\authclient\BaseClient;
  use yii\web\Response;

  class MyClient extends BaseClient implements CustomAuth {
      public function handleAuthRequest(): ?Response {
          // custom logic. Return a Response for a redirect (e.g. SP → IdP),
          // or null to signal completion — AuthAction calls authSuccess()
          // automatically.
      }
  }
  ```
  `humhub\modules\user\authclient\AuthAction` no longer falls back on the legacy marker — custom auth clients that didn't migrate will throw a `NotSupportedException` at dispatch time.
- Renamed `humhub\modules\user\authclient\BaseFormAuth` to `BaseFormClient`. The previous name doubled the "Auth" suffix with the surrounding `authclient/` namespace — the new name mirrors Yii's `BaseClient` parent. Drop-in rename; the class lives in the same namespace.
- Added `humhub\modules\user\authclient\interfaces\SingleLogout` — capability marker for AuthClients that support Single Logout (terminating the user's session at the identity provider, not just locally). `AuthController::actionLogout()` calls `$client->singleLogout(): ?Response` on the user's current AuthClient before tearing down the local session; a returned Response (typically a redirect SP → IdP) short-circuits, the IdP eventually redirects back to a module-owned callback URL that finalises the local logout. Modules previously implementing SLO via `EVENT_BEFORE_ACTION` interception on `AuthController` (saml-sso) should migrate to the interface and drop the event hook.
- Added `humhub\modules\user\authclient\interfaces\PasswordAuth` — declares the contract for AuthClients that authenticate via the login form (password-based):
  ```php
  interface PasswordAuth {
      public function authenticate(string $username, string $password): bool;
  }
  ```
  `BaseFormClient` and its subclasses (Password, LdapAuth) now implement it. The old stateful pattern — set `$client->login = $loginForm`, then call `$client->auth()` — is replaced by explicit parameter passing. Custom form-auth modules need to rename `auth()` → `authenticate(string, string): bool` and read credentials from the parameters instead of `$this->login->...`. Implementations must still call `setUserAttributes()` on success so the downstream lookup in `AuthClientService::getUser()` works; the `User` lookup itself no longer happens inside `authenticate()`.
- `humhub\modules\user\authclient\AuthAction` now dispatches `CustomAuth` clients before falling through to the OAuth/OpenID families. `AuthController::actions()['external']` uses the HumHub AuthAction class again.
  - The `rememberMe` query-parameter handling (writing to `loginRememberMe` session key) is removed; remember-me for OAuth/SSO clients was never supported anyway
- Removed `humhub\modules\user\jobs\SyncUsers` — was deprecated since 1.16; register a dedicated sync job in your module instead (see `humhub\modules\ldap\jobs\LdapSyncJob` as example)
  - `humhub\modules\user\authclient\interfaces\AutoSyncUsers` is kept for now but no longer called by core — implement a dedicated queue job instead
- Removed from `humhub\modules\user\services\AuthClientService`:
  - `createRegistration()` — use `createUser()` instead
  - `legacySyncAttributes()` — implement `UserSourceInterface::updateUser()` on your UserSource instead
- Removed from `humhub\modules\user\services\AuthClientUserService`:
  - `getPrimaryAuthClient()` — use `UserSourceService::getForUser($user)->getUserSource()` instead
  - `canChangePassword()`, `canChangeEmail()`, `canChangeUsername()`, `canDeleteAccount()`, `getSyncAttributes()` — use `UserSourceService` directly
- Removed from `humhub\modules\user\models\User`:
  - `getUserSourceService()` — use `UserSourceService::getForUser($user)` instead
- Refactored `ldap` module: replaced `laminas/laminas-ldap` with `directorytree/ldaprecord`
  - Removed class `humhub\modules\ldap\components\ZendLdap`
  - Removed from `humhub\modules\ldap\authclient\LdapAuth`:
    - Property `$loginFilter`
    - Methods `getLdap()`, `setLdap()`, `getUserNode()`, `getUserDn()`, `getUserCollection()`, `getAuthClientInstance()`
  - Added `humhub\modules\ldap\services\LdapService` as the new LDAP connection and query layer
  - Added `LdapAuth::getLdapService()` returning `LdapService`
  - `cleanLdapResponse()` moved from `LdapService` to `humhub\modules\ldap\helpers\LdapHelper`
- Refactored `ldap` module to the connection-registry pattern (reference implementation of the UserSource architecture):
  - New `humhub\modules\ldap\connection\LdapConnectionConfig` — plain value object holding hostname, port, baseDn, bindDn, attribute mappings, etc.
  - New `humhub\modules\ldap\connection\LdapConnectionRegistry` — keyed registry of connections; instantiated lazily on `Module::getConnectionRegistry()`. The default `'ldap'` connection is populated from the DB-backed `LdapSettings` UI; additional read-only connections can be added via `protected/config/common.php` → `modules.ldap.connections.<id> = [...]`.
  - `humhub\modules\ldap\authclient\LdapAuth` is now a vanilla `BaseFormClient` that references its connection by ID (`$connectionId`). Connection parameters (hostname/port/baseDn/bindUsername/bindPassword/userFilter/idAttribute/usernameAttribute/emailAttribute/languageAttribute/ignoredDNs/networkTimeout/disableCertificateChecking/autoRefreshUsers/syncUserTableAttributes) are gone — read them from `LdapConnectionConfig` via `LdapAuth::getConfig()` if needed.
  - `LdapAuth` no longer implements `SerializableAuthClient` — it carries no connection state.
  - `LdapUserSource` is registered once per connection. The constructor takes `$connectionId` (not an `LdapAuth` instance). Sync uses the registered AuthClient for attribute normalisation but doesn't own it.
  - `LdapService` constructor now takes `LdapConnectionConfig` (was `LdapAuth`). Obtain instances via `Module::getConnectionRegistry()->getService($connectionId)`. The static `LdapService::create()` factory and `LdapService::getAuthClients()` are removed — use `LdapConnectionRegistry::getService($id)` and `LdapService::getAllUserEntries()` respectively.
  - `LdapSettings::getLdapAuthDefinition()` and `getLdapUserSourceDefinition()` removed — use `LdapSettings::getConnectionConfig()` which returns an `LdapConnectionConfig`.
  - `LdapAuth::$connectionId` and `LdapUserSource::$connectionId` are now required (no default `'ldap'` fallback) — instantiating either class without a connection ID throws `InvalidConfigException`. The bootstrap registers them per connection ID from the registry.
  - The LDAP UserSource is now registered via its own event hook on `UserSourceCollection::EVENT_BEFORE_USER_SOURCES_SET` (`Events::onUserSourceCollectionSet`), separate from the AuthClient registration on `Collection::EVENT_BEFORE_CLIENTS_SET`. The two collections are no longer coupled through a single event handler.
- `MigrateController::$includeModuleMigrations` is now `true` by default
- **Module migrations run with the module registered** — `migrate/up` fully registers each enabled module before applying its migrations (namespace alias from `config.php`, module instance via `Yii::$app->getModule('<id>')`), matching the web-based migration. A module that fails to register (e.g. still referencing removed core classes during an upgrade) is skipped with a warning; its migrations run when the module itself is updated. See [Migrations and module context](concept-models.md#migrations-and-module-context).
- SiteIcon: Remove support for manually uploaded `@web/uploads/icon/` icons
- New `AssetImage` class
  - `LogoImage`, `SiteIcon`, `LoginBackground`, `MailHeader`, `ProfileImage`, `ProfileBannerImage` are now deprecated or removed.
    - `Space|User::getProfileImage()` => `Space|User::getImage()` / `$container->image` (returns `AssetImage`); `getProfileImage()` itself remains as a deprecated shim returning `humhub\libs\ProfileImage`
    - `Space|User::getProfileBannerImage()` => `Space|User::getBannerImage()` / `$container->bannerImage` — **removed without a shim**: `getProfileBannerImage()`, the `profileBannerImage` property and the `humhub\libs\ProfileBannerImage` class no longer exist; modules using them fatal until migrated
    - `SiteLogo|SiteIcon|LoginBackground|MailHeader::getUrl()` => `Yii::$app->img->logo|icon|loginBackground|mailHeader->getUrl()`
- `AssetManager::forcePublish()` removed
- Removed `@filestore` Alias
- Removed `AssetManager::$preventDefer` option
- Removed the webroot `static/` asset tree — core static resources moved to `protected/humhub/resources` and are published through the asset manager (#8102). Core themes moved from the webroot `themes/` directory to `protected/humhub/themes`; custom themes in `@webroot/themes` keep working, the `@themes` alias is unchanged.
  - `Theme::getBasePath()` keeps pointing at the theme *source* directory — `@humhub/themes` for the core theme, `<module>/themes` for module provided themes, `@themes` (webroot) for custom themes. `views/` and `scss/` are built from there and are excluded from publishing, so anything served to the browser is only reachable below the new `Theme::getPublishedBasePath()`, the published counterpart of `getBasePath()` (alongside the existing `Theme::getPublishedResourcesPath()`).
    - `getPublishedBasePath()` and `getPublishedResourcesPath()` return a path **relative to the assets mount**, to be used with `Yii::$app->assetManager` or `Yii::$app->fs->getAssetsMount()` — not with native filesystem functions such as `is_file()` or `file_get_contents()`, since the assets mount may be remote (e.g. S3). Use `AssetManager::fileExists()`, `fileWrite()` and the new `AssetManager::fileLastModified()` (the counterpart of `filemtime()`) instead. `getPublishedResourcesPath()` previously returned an absolute path or a relative one depending on whether the theme had already been published in the same request.
    - `Theme::getPath()` is **not** overridden and still resolves against the theme source directory, per its Yii2 contract of returning an absolute local filesystem path — which published assets cannot satisfy on a remote mount. Use `getPublishedBasePath()` with the asset manager for published files, or `Theme::getUrl()` for a browser-facing URL.
    - Theme resources are **not** inherited from parent themes: `publishResources()` publishes only the theme's own directory and `getBaseUrl()`/`getPublishedBasePath()` only ever point at it, so a child theme has to ship every resource it references (unlike `views/`, which do fall back through the theme tree).
  - Removed the aliases `@web-static` / `@webroot-static` and the `humhub\components\assets\WebStaticAssetBundle` class. Asset bundles configuring `basePath = '@webroot-static'` / `baseUrl = '@web-static'` must switch to publishing via `$sourcePath` instead (e.g. `'@humhub/resources'` like the core bundles, or the module's own `resources/` directory).
- New Flysystem Filesystem Wrapper - Migrate all file access for assets and uploads to the Flysystem wrapper (`Yii::$app->fs->getDataMount()` or `Yii::$app->fs->getAssetsMount()`). Read more: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
  - **`StorageManager::get()` (`$file->store->get()`) now returns a path relative to the data mount, no longer an absolute local filesystem path.** The same applies to `FileHistory::getFileStorePath()`. Any code treating the return value as a local path — `is_file()`, `file_exists()`, `file_get_contents()`, `filesize()`, `hash_file()`, `fopen()`, `Imagine::open()`, `Response::sendFile()`/`xSendFile()`, archive/scanner APIs — silently breaks (checks return `false`, reads fail) and must be migrated:
    - Read/write through the store API: `$file->store->getContent()`, `getContentStream()`, `setContent()`, `has()`, `fileSize()`, `mimeType()` — or use the Flysystem instance `$file->store->fs` directly. These work with any mount (local or remote/S3).
    - Image processing: `Image::getImagine()->load($file->store->getContent())` instead of `open()`; persist with `$file->store->setContent($image->get($format, $options))` instead of `$image->save()` (see `humhub\modules\file\libs\ImageHelper`).
    - `exif_read_data()` accepts a stream: pass `$file->store->getContentStream()`.
    - Only when serving a file via a web-server mechanism that requires a real local path (e.g. X-Sendfile): verify `Yii::$app->fs->getDataMountConfig() instanceof LocalMountConfig` and prefix `Yii::getAlias($dataMountConfig->path)` (see `humhub\modules\file\actions\DownloadAction`); otherwise fall back to `sendStreamAsFile()`.
  - `File::setStoredFile()` with a string argument now expects a **data-mount-relative** path (it is read via Flysystem). Passing an absolute local path (e.g. a temp file) now throws `Invalid parameter type.` — use `setStoredFileContent(file_get_contents($localPath))` or an `UploadedFile` instead.
  - `humhub\modules\file\components\StorageManagerInterface` was extended and strictly typed: new required methods `getContent()`, `getContentStream()`, `checksum()` and `mimeType()`, plus parameter/return types on all existing methods. A custom storage manager (configured via the file module's `$storageManagerClass`) fails on load until it implements the new methods with matching signatures.
- Added `humhub\modules\content\components\ContentContainerActiveRecord::EVENT_INIT_PROFILE_IMAGE`
  and `EVENT_INIT_BANNER_IMAGE` (`humhub\modules\content\events\ContentContainerImageEvent`) to customize
  or replace a container's profile/banner `AssetImage`. Use these instead of overriding `$profileImageClass`,
  which only affects the deprecated `ProfileImage` path.
- **Impersonation no longer grants access to private content.** Until now an admin impersonating a user saw
  everything that user sees. Private content and private spaces are now hidden while impersonating, and each
  impersonation is written to the log (category `user`). Both are configurable via the new
  `humhub\modules\user\components\Impersonation` component (`Yii::$app->user->impersonation`):

  ```php
  'components' => [
      'user' => [
          'impersonation' => [
              'allowPrivateContentAccess' => true, // pre-1.19 behavior
              'log' => false,
          ],
      ],
  ],
  ```

  - Core enforces the restriction in `Content::canView()`, `ActiveQueryContent::readable()`,
    `StreamQuery::setupQuery()`, `Space::canAccessPrivateContent()`, `User::canAccessPrivateContent()`,
    `ActiveQuerySpace::visible()`, the space controller behavior and both content search drivers — content
    and spaces a module lists through those APIs are already covered. A module running **its own** visibility
    SQL (a stream filter or search backend that does not go through `ActiveQueryContent`/`StreamQuery`) must
    check `Yii::$app->user->impersonation->canAccessPrivateContent()` itself and restrict its query to
    `Content::VISIBILITY_PUBLIC`.
  - Added the `ControllerAccess::RULE_DENY_IMPERSONATED` access rule. A module whose controller exposes
    private data that is not `Content`-based (private messages, private files, …) should add
    `[ControllerAccess::RULE_DENY_IMPERSONATED]` to its `getAccessRules()` — the action is then denied
    while the impersonation restriction applies. Menu entries and widgets pointing at such a controller
    should be hidden by checking `Yii::$app->user->impersonation->canAccessPrivateContent()` in their
    event handler. See the Messenger (`mail`) module, which applies both.
  - The `humhub\modules\user\components\Impersonator` behavior of the user component was **removed**
    (no known module usage): `Yii::$app->user->impersonate($user)` → `Yii::$app->user->impersonation->start($user)`,
    `restoreImpersonator()` → `impersonation->stop()`, `isImpersonated` → `impersonation->isActive()`,
    `getImpersonator()` → `impersonation->getImpersonator()`, `canImpersonate($user)` → `impersonation->canStart($user)`.
    The model method `humhub\modules\user\models\User::canImpersonate()` was removed as well, its logic
    now lives in `Impersonation::canStart()`.
  - The impersonation state is bound to the session and fails closed: the impersonated identity never
    receives an auto-login cookie, only the impersonator's user id is stored in the session (no serialized
    record anymore), and ending an impersonation whose impersonator can no longer be resolved logs the
    session out instead of silently continuing as the impersonated user.
- Removed the unreachable `ContentContainerControllerAccess::RULE_CONTAINER_ACCESS`
  (`'containerAccess'`) rule and its validator `validateContainerAccess()`, along with the
  private helpers it alone used (`canAccessSpace()`, `getSpaceMembership()`, `canAccessUser()`,
  the `$_membership` property). The rule was registered but never added to any
  `getAccessRules()`/`getFixedRules()`, so it never ran — the space and profile visibility
  checks it duplicated are already enforced unconditionally by `space\behaviors\SpaceController`
  and `user\behaviors\ProfileController` on `EVENT_BEFORE_ACTION`. No known module referenced
  the rule. A module that added `[ContentContainerControllerAccess::RULE_CONTAINER_ACCESS]` to
  its own `getAccessRules()` must remove it; the behaviors already provide equivalent
  enforcement.
- Removed the `$right` parameter from `humhub\widgets\bootstrap\BootstrapVariationsTrait::icon()`
  (used by `Button`, `Badge`, `Link` and `Alert`). The parameter was never read by the method —
  right-aligning an icon already goes through the separate `->right()` method — so this only
  changes the signature: `icon(string|Icon|null $icon, $options = [])`, `$options` is now the
  **second** parameter instead of the third.
  **Warning:** PHP silently ignores extra arguments, so a call like `->icon('user', true)` made
  for the old `$right` flag does not fail — `true` is now passed as `$options` to `Icon::get()`,
  which fatals with "Cannot use a scalar value as an array". Audit every `->icon(...)` call that
  passes a second argument and drop it, or move a third-argument `$options` array into the second
  position.
  - `humhub\modules\ui\menu\MenuLink::setIcon()` also dropped its `$right` parameter for the same
    reason (it only forwarded into `icon()`, and `right()` on the underlying `Button`/`Badge` is
    the correct way to align an icon). `setIcon($icon, $right)` → `setIcon($icon)`.
    **Warning:** PHP silently ignores the now-unused second argument instead of erroring, so a
    call like `$menuLink->setIcon('user', true)` keeps "succeeding" but the icon is no longer
    right-aligned — audit every `->setIcon(...)` call that passes a second argument and call
    `->getLink()->right()` explicitly if right-alignment is still needed.
- **Removed** `humhub\components\assets\AssetBundle::$defaultDepends` (unused by any known module).
  It promised that a bundle implicitly depends on `CoreBundleAsset` without listing it in
  `$depends`, but the code applying it read a misspelled property (`dependsDefault`) and has
  therefore never run since it was added in 1.5 (#3941). It also cannot be repaired as written:
  with the property name corrected, every bundle inside `CoreBundleAsset`'s own dependency tree
  depends on it and Yii aborts the request with `A circular dependency is detected for bundle
  'humhub\assets\CoreBundleAsset'`. A bundle needing the core bundle lists it in `$depends`
  itself, which is what bundles have been doing all along. The `public $defaultDepends = false;`
  opt-out in core bundles is gone with it; a module declaring the property keeps working (it
  becomes an unused own property), but a bundle **configured** with `'defaultDepends' => ...`
  now fails with `Setting unknown property`.

## Released versions

- [Version 1.18](module-migrate-1.18.md) — captcha framework, Codeception 5, mailer config keys
- [Version 1.17](module-migrate-1.17.md) — Manage-All-Content permission, CSS variables
- [Version 1.16](module-migrate-1.16.md) — search refactor, PHP 8.0 minimum
- [Version 1.15](module-migrate-1.15.md) — JS nonces, type restrictions, GUID validation
- [Legacy versions (1.14 and earlier)](module-migrate-legacy.md)
