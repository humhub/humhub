# Vue.js Legacy Interop

> Part of the [Vue.js integration](ui-js-vuejs.md) documentation. This chapter covers the patterns islands use to bridge into pre-existing jQuery widgets and legacy enhancers, rather than rewriting them in Vue: the `v-additions` directive, `RichTextOutput`, the form-shell pattern and `LegacyFormWrapper`, `timeago`/`[data-ui-addition]`, delegated legacy actions, and known caveats of the approach. For motivation, goals, constraints and the overall architecture, see the [overview](ui-js-vuejs.md).

## The `v-additions` directive

Every island app registers a small custom directive, `v-additions`, that hands its element to the legacy `ui.additions` enhancer pipeline — the same interop every server-rendered fragment gets on PJAX navigation, modal open and stream reloads (richtext output, gallery previews, tooltips, widget auto-init via `[data-ui-init]`, `[data-ui-addition]` targets, ...):

```js
app.directive('additions', {
    mounted: function (el) { additions.applyTo($(el)); },
    updated: function (el) { additions.applyTo($(el)); },
});
```

It runs on **both** `mounted` and `updated`, not just once on mount — genuinely changed content (e.g. an edited comment's re-rendered richtext output) needs the enhancers re-applied, which is the point of the `updated` hook, not just a safety margin.

**Idempotency expectations.** Because `v-additions` can legitimately fire many times against the same element, every addition it triggers needs to tolerate repeat application:

- Widget instantiation is cached per node (`Component._getInstance()` in `humhub.action.js` returns the existing instance instead of re-creating it) — safe by construction.
- Bootstrap tooltips aren't part of the `applyTo()` pipeline at all: they're created lazily by a document-level mouseover listener in `humhub.ui.additions.js`, guarded by `Tooltip.getInstance()` — unaffected either way.
- `showMore` (`humhub.ui.showMore.js`) used to stack a duplicate, unnamespaced click handler on every re-apply to the same "Read more" button; it was fixed to be re-apply-safe (namespaced, unbound before rebound) specifically because Vue-rendered, repeatedly-`updated` content like comment entries is exactly what exercises this path.
- A couple of additions (`select2`, `highlightCode`) aren't demonstrably idempotent on repeat calls, but aren't expected on island-rendered content, and already carry the same repeat-apply exposure today wherever legacy code reloads DOM via `ui.additions` (e.g. `Widget.prototype.replace()`).

Use `v-additions` on any island subtree that renders legacy-enhanced markup — either directly (as `CommentEntry.vue`'s root does, for its `timeago`/tooltip children) or inside a component like `RichTextOutput`/`LegacyFormWrapper` below that exists specifically to host such markup.

## RichTextOutput

`RichTextOutput` (see [Components: core component set](ui-js-vuejs-components.md#core-component-set)) renders a rich text message inside an island — the client-side counterpart of `RichText::output()`, whose envelope it renders itself:

```html
<div v-if="message && ready" v-additions>
    <div :key="message" data-ui-richtext data-ui-widget="ui.richtext.prosemirror.RichText" data-ui-init>{{ message }}</div>
</div>
```

It is a generic core interop component — any module's island can embed it wherever it would otherwise render a richtext message; the comment section (`CommentEntry.vue`) is the reference consumer, passing through its own attributes (`class="comment-message"`, `data-ui-markdown`, `data-ui-show-more`, `:data-read-more-text`) via Vue's attribute fallthrough onto the component's own root, one level above the envelope div — rather than an extra wrapping element.

**Props.** `message` (String) is the processed markdown an API payload ships — `RichText::outputMarkdown()`: run through every extension's `onBeforeOutput()` hook (mention resolution, legacy-compat rewriting, ...) but deliberately **not** HTML-encoded (the format and the pipelines behind it are described in [Rich text](concept-richtext.md)). It is the component's only prop. The envelope's attributes are constants: `data-ui-widget` + `data-ui-init` boot the richtext display widget (`humhub.ui.richtext.prosemirror.js`), `data-ui-richtext` is what theme CSS and other modules select richtext content by, and the plugin preset is the widget's default for content output — there are no per-message render options. The envelope's own auto-generated widget `id` is never reconstructed — it was always a meaningless per-render DOM-uniqueness counter that neither the display widget nor theme CSS ever read (`[data-ui-richtext]`, not an id, is what locates richtext content).

**XSS: `message` is text, never raw HTML.** `{{ message }}` is Vue text interpolation — it always creates an escaped DOM text node, the client-side equivalent of the `Html::encode($output)` `ProsemirrorRichText::run()` applies before wrapping the envelope. A literal `<script>` (or any other markup) inside a message therefore renders as inert text, never a real element — this component never uses `v-html`.

**Oembed previews — fetched by the viewer, not shipped.** The display widget renders an `[url](oembed:url)` link as the preview `humhub.oembed.js` `get(url)` knows at render time, or as a plain link if it knows none. A server-rendered richtext ships the previews as a hidden `.richtext-oembed-container` sibling of its envelope (`OembedExtension::onAfterOutput()`). An API payload does not, because which preview a reader gets depends on the reader — with the oembed consent setting on, a member who has not allowed the domain gets the confirmation prompt where a guest gets the media — and the payloads are caller-neutral, and cached as such (see [HTTP API framework](concept-api.md)). `RichTextOutput` therefore collects the oembed urls of the message (at most `OembedExtension::$maxOembed`), calls `oembed.load(urls, { consent: true, silent: true })` — the `@humhub/vue` bridge onto `humhub.oembed.js`, which posts to `oembed/index`; `consent=1` makes the server honor the viewer's consent instead of forcing the media as it does for the editor's paste preview — and mounts the envelope once the previews sit in that module's cache. A message with oembed links thus appears one request later, a message without renders at once; if the loader fails, the message renders with plain links and a warning is logged. Every other extension (mentioning, file links, emoji) needs nothing of the kind: its contribution lives in the markdown text.

`v-additions` boots the legacy richtext DISPLAY widget on the envelope div — and any other legacy enhancer targeting the subtree (`timeago`, mentions, oembed lookup, ...) — on mount, and re-runs it on update so a re-rendered message (e.g. after editing a comment) is picked up again. The widget caches its instance on the envelope node and initializes once per node, which is why the envelope is keyed by `message`: a new message mounts a new node instead of swapping the text of one the widget has already rendered. The component is deliberately classless: the caller owns layout and styling of the rendered output.

## Form-shell pattern: VueFormShell

> As of the [Form suite](ui-js-vuejs-forms.md), a module building a Vue island form should
> reach for `HumHubForm` + its native field components first, and only fall back to the
> pattern below (via `RichTextField`, the suite's own legacy-citizen field) for widgets too
> deep to rewrite in Vue — this section documents that underlying mechanism, which
> `RichTextField` embeds unchanged rather than duplicates.

Some server-rendered widgets are too deep to rewrite in Vue in one pass (rich text editors, file uploaders with drag/drop, progress and previews). `humhub\widgets\VueFormShell` is the reusable core mechanism for wrapping one inside an island without server-rendering a form per instance:

1. `VueFormShell::widget(['content' => function (ActiveForm $form) { ... }])` renders a bare `ActiveForm` shell: the widget owns `ActiveForm::begin()`/`::end()` and its own conventions (`action => '#'`, CSRF input disabled, `acknowledge => true`), while the `content` closure renders whatever fields the caller needs, using the `ActiveForm` instance the widget already began. Every id the closure declares or references (`id`, `for`, and CSS-id-selector fragments embedded in `data-*` attribute values) is built from one literal placeholder token via the `VueFormShell::id('suffix')` static helper, instead of a real id.
2. That HTML string travels once in the island's initial props (e.g. `formShellHtml`) — the shell is rendered once, not once per form instance, even though a page can host many instances of it at once (a create form, one open reply form per commented-on entry, an edit form).

```php
echo VueFormShell::widget([
    'content' => function (ActiveForm $form) use ($model) {
        return $form->field($model, 'title', [
            'options' => ['id' => VueFormShell::id('title-group')],
        ])->textInput(['id' => VueFormShell::id('title')])->label(false);
    },
]);
```

The comment form (`humhub\modules\comment\widgets\CommentFormShell`, backed by `comment/widgets/views/commentFormShell.php`) is the reference composition on top of this mechanism: it wraps `VueFormShell::widget()`'s output in its own comment-specific markup (a drop-zone container, `<hr>`) and supplies a `content` closure rendering the richtext editor — uploads are a native `UploadField` next to the shell (see [Form suite: file uploads](ui-js-vuejs-forms.md#file-uploads)); everything comment-specific stays in that widget/view; `VueFormShell` itself knows nothing about comments.

See [LegacyFormWrapper](#legacyformwrapper) below for how the client turns that one shell into as many independent, collision-free form instances as needed.

## LegacyFormWrapper

`LegacyFormWrapper` (see [Components: core component set](ui-js-vuejs-components.md#core-component-set)) is the client half of the form-shell pattern: a small wrapper component that hosts a [`VueFormShell`](#form-shell-pattern-vueformshell)-rendered server shell inside an island and exposes a small, clean API to the surrounding Vue component so it never has to touch jQuery/legacy widgets itself. The [Form suite](ui-js-vuejs-forms.md)'s `RichTextField` is built directly on top of this component (see its own "Legacy fields" section) rather than duplicating its logic — this section remains the authoritative reference for what `LegacyFormWrapper` itself does.

**`__VUEFORM__` token contract.** The server-rendered shell carries the literal token `__VUEFORM__` (`VueFormShell::TOKEN` server-side) everywhere an id is declared *or* referenced (`id`, `for`, and any `data-*` attribute value that embeds an id, including CSS-id-selector fragments like `#__VUEFORM___comment_create_form`, from `VueFormShell::id('comment_create_form')`) — not just in `id="..."` attributes themselves. Note the token is a **prefix** (`VueFormShell::id()` returns `TOKEN . '_' . $suffix`), not a suffix. `LegacyFormWrapper` binds the shell with `v-html` plus `v-additions` (booting the legacy widgets the same way any other injected fragment does), after replacing every occurrence of the token with a unique id from a module-scope counter (not `Math.random()`, so `vue.build`'s output stays deterministic) — so the SAME shell can be cloned as many times on the page as needed (a create form, several open reply forms, an edit form) without id collisions. The literal token is a mirrored pair across languages: `VueFormShell::TOKEN` (PHP) and `LegacyFormWrapper.vue`'s own `FORM_TOKEN` constant must always agree.

**Widget-instance APIs used.** The wrapper exposes a small, typed API (`getValue()`/`setValue()`/`clear()`/`focus()`, plus `resetAcknowledge()` for the unsaved-changes guard) that reads the booted widget instances directly off their own cached jQuery data (`Component.prototype.init`'s `this.$.data(this.static('component'), this)` key) instead of exporting jQuery/legacy globals to the rest of the Vue tree. It has no file API any more: uploads are a native field of their own (`UploadField`).

This keeps the deep interactive editing surface exactly as-is while the surrounding list/state/mutation logic is plain, testable Vue.

## `timeago` and `[data-ui-addition]`

`CommentEntry.vue` renders its relative timestamp with the same markup `TimeAgo::renderTimeAgo()` produces server-side:

```html
<time class="tt time timeago" data-ui-addition="timeago" :datetime="comment.createdAt" :title="absoluteTime">{{ absoluteTime }}</time>
```

The `timeago` addition is registered selector-less in `humhub.ui.additions.js` (see [UI additions: register addition without selector](ui-js-uiadditions.md#register-addition-without-selector-since-v14)) and dispatched per-element through the generic `[data-ui-addition]` addition — any Vue-rendered element carrying `data-ui-addition="<name>"` gets the exact same treatment a server-rendered one would, through the same `v-additions` directive described above. No island-specific wiring is needed for this class of legacy addition at all.

The text Vue renders initially (`new Date(comment.createdAt).toLocaleString()`) is only ever visible for an instant: `v-additions` runs the real `timeago` addition on mount, which immediately overwrites it with a live relative time. This client-side formatting is a documented parity gap against the server/profile-timezone-formatted absolute time a full page render would use — acceptable precisely because the fallback text is transient.

## Delegated legacy actions

Some entry points are simplest left as plain legacy actions rather than new Vue-owned handlers. `CommentControls.vue`'s permalink entry, for example, is a plain anchor reusing the exact legacy attributes a server-rendered version would carry:

```html
<a
    href="#"
    class="dropdown-item"
    data-action-click="content.permalink"
    :data-content-permalink="permalink"
    :data-content-permalink-title="permalinkTitle"
>{{ permalinkLabel }}</a>
```

This works with zero extra wiring because `humhub.action.js` binds the `[data-action-click]` delegate on `document` itself, so it already fires for anchors injected anywhere in the DOM — Vue-rendered islands included — as long as the module owning the action (`content`/`ui.content` here) is already loaded page-wide wherever the island can appear, which it is for every page comments can render on. Reach for this pattern instead of a new Vue `@click` handler whenever an existing, page-wide-delegated legacy action already does exactly what's needed.

## Status messages

The user-feedback bar is a Vue island (`StatusBar`, see [Components: core component set](ui-js-vuejs-components.md#core-component-set)), but every one of its callers is legacy and stays that way:

- `humhub.ui.status`'s exported `success`/`info`/`warn`/`error` (18 external modules call them), plus its `humhub:modules:log:setStatus` listener, which is how `humhub.log.*(msg, details, true)` surfaces.
- the inline `humhub.modules.ui.status.<type>(…)` snippet `humhub\components\View::endBody()` registers at `POS_END` for a session flash message (`$this->view->success(…)`).

`humhub.ui.status` is therefore a façade: it flattens the `details` argument (the shapes only legacy code can recognise — a `client.Response`, a jQuery-style `{error: Error}` envelope) into a plain string and forwards everything to the bridge:

```js
require('vue').status(level, message, details, closeAfter);
```

The bridge (`humhub.vue.js`) **queues** messages until an island registers itself as the handler:

```js
status(level, message, details, closeAfter)   // callers
setStatusHandler(fn | null)                   // StatusBar.vue, on mount / unmount
```

That queue is what makes the flash-message path work at all: the inline snippet runs at `POS_END`, long before `ui.additions` sweeps the document and mounts the island. It replaces the `module.initState` hand-off the legacy module did internally. A page without the island (the installer layout renders no `LayoutAddons`) simply accumulates messages nobody shows — the same net effect the legacy module had without its markup present.

Reach for this pattern — a bridge-level queue plus a handler an island registers — whenever an island has to receive imperative calls from legacy code that may run before it exists. Note the two conventions it follows: the legacy module keeps its public API (no caller changes), and the island's own contract stays declarative (no globals, no DOM lookups).

Two deliberate consequences for callers:

- **Messages render as text.** The bar interpolates the message (`{{ }}`), it no longer injects it as HTML. For the caller that pre-escaped its text — the common legacy case, since the jQuery bar used `.html()` — the façade decodes the entities `Html::encode()` produces, so those callers keep reading correctly. Markup a caller passes deliberately now shows up as text; see [module-migrate-1.20.md](module-migrate-1.20.md).
- **`View::endBody()` ships the raw message** as a JSON string literal. The former HTML-encode-and-interpolate path needed a `&quot;` strip to stay syntactically valid, which silently deleted every double quote from a flash message.

## Known caveats

**Asset registration in JSON fragments.** Oembed previews (see [RichTextOutput](#richtextoutput) above) reach the page as HTML fragments in a JSON response (`oembed/index`), not through a full page render. (Comment attachments are no longer an HTML fragment at all — the API's `files` shape is rendered client-side by `AttachedFiles.vue`.) Any `AssetBundle::register($view)` or `View::registerJs()` call made while building such a fragment has no effect — `UrlOembed::getOEmbed()`'s re-injection of a provider's `<script>` is one: assets only reach the response when a layout is rendered (`View::head()`/`endBody()`), which never happens on a pure JSON action. Content that arrives this way therefore depends entirely on its script/style needs already being satisfied by a bundle the *containing page* loaded some other way — a module widget that assumes its own, not-already-page-wide `AssetBundle::register()` call will take effect cannot be embedded this way without first getting that asset onto the page through some other path.

**Richtext render events don't fire on this path.** `message` is built via `RichText::outputMarkdown()`, which never calls `run()` — so `AbstractRichText::EVENT_AFTER_RUN`/`EVENT_AFTER_OUTPUT` (the HTML-append hooks a normal `RichText::output()` call fires) never fire for a comment message. This is an accepted consequence of client-side rendering, not a bug; see [module-migrate-1.20.md](module-migrate-1.20.md) for the known affected modules and the Vue-side migration path (`registerSlotComponent`/`ExtensionSlot` or `registerMenuEntry`, fed by the module's own endpoint).
