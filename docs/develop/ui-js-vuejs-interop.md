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

`RichTextOutput` (see [Components: core component set](ui-js-vuejs-components.md#core-component-set)) renders a server-generated RichText envelope inside an island:

```html
<div v-if="output" v-html="output" v-additions></div>
```

It is a generic core interop component — any module's island can embed it wherever it would otherwise `v-html` a `RichText::output()` string; the comment section (`CommentEntry.vue`) is the reference consumer, passing through its own attributes (`class="comment-message"`, `data-ui-markdown`, `data-ui-show-more`, `:data-read-more-text`) via Vue's attribute fallthrough rather than an extra wrapping element.

**Trusted-source warning.** `output` **must** be trusted, server-generated HTML produced by `RichText::output()` (see `AbstractRichTextEditor::editOutput()`) — **never** raw or untrusted user input. It is bound with `v-html`, and the envelope's markdown source (carried in `data-*` attributes inside it) is rendered to HTML entirely client-side by the legacy richtext addition, exactly as happens today for server-rendered content. `v-additions` boots that addition — and any other legacy enhancer targeting the subtree (`timeago`, mentions, oembed, ...) — on mount, and re-runs it on update so a re-rendered envelope (e.g. after editing a comment) is picked up again. The component is deliberately classless: the caller owns layout and styling of the rendered output.

## Form-shell pattern: CommentFormShell

Some server-rendered widgets are too deep to rewrite in Vue in one pass (rich text editors, file uploaders with drag/drop, progress and previews). The comment form is the reference example for wrapping one inside an island without server-rendering a form per instance:

1. A PHP widget (`humhub\modules\comment\widgets\CommentFormShell`) renders the widget markup exactly as before, but every element id it declares or references (`id`, `for`, and CSS-id-selector fragments embedded in `data-*` attribute values) is built from one literal placeholder token instead of a real id.
2. That HTML string travels once in the island's initial props (e.g. `formShellHtml`) — the shell is rendered once, not once per form instance, even though a page can host many instances of it at once (the main comment form, one open reply form per commented-on entry, an edit form).

See [LegacyFormWrapper](#legacyformwrapper) below for how the client turns that one shell into as many independent, collision-free form instances as needed.

## LegacyFormWrapper

`LegacyFormWrapper` (see [Components: core component set](ui-js-vuejs-components.md#core-component-set)) is the client half of the form-shell pattern: a small wrapper component that hosts a [`CommentFormShell`](#form-shell-pattern-commentformshell)-style server-rendered shell inside an island and exposes a small, clean API to the surrounding Vue component so it never has to touch jQuery/legacy widgets itself.

**`__VUEFORM__` token contract.** The server-rendered shell carries the literal token `__VUEFORM__` everywhere an id is declared *or* referenced (`id`, `for`, and any `data-*` attribute value that embeds an id, including CSS-id-selector fragments like `#comment_create_form___VUEFORM__`) — not just in `id="..."` attributes themselves. `LegacyFormWrapper` binds the shell with `v-html` plus `v-additions` (booting the legacy widgets the same way any other injected fragment does), after replacing every occurrence of the token with a unique id from a module-scope counter (not `Math.random()`, so `vue.build`'s output stays deterministic) — so the SAME shell can be cloned as many times on the page as needed (a create form, several open reply forms, an edit form) without id collisions.

**Widget-instance APIs used.** The wrapper exposes a small, typed API (`getValue()`/`setValue()`/`clear()`/`focus()`/`getFileGuids()`) that reads the booted widget instances directly off their own cached jQuery data (`Component.prototype.init`'s `this.$.data(this.static('component'), this)` key) instead of exporting jQuery/legacy globals to the rest of the Vue tree.

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

## Known caveats

**Asset registration in JSON fragments.** `messageOutput` (`RichText::output()`) and `attachmentsHtml` (`ShowFiles::widget()`) are rendered by `CommentJsonService` into plain HTML strings returned from a JSON action, not through a full page render. Any `AssetBundle::register($view)` call a widget makes while building that HTML has no effect: assets only reach the response when a layout is rendered (`View::head()`/`endBody()`), which never happens on a pure JSON action. Content that arrives this way therefore depends entirely on its script/style needs already being satisfied by a bundle the *containing page* loaded some other way — a module widget that assumes its own, not-already-page-wide `AssetBundle::register()` call will take effect cannot be embedded into a comment this way without first getting that asset onto the page through some other path.

**oembed note.** In practice this rarely bites today: `humhub.oembed.js` ships in `CoreExtensionAsset`, part of `CoreBundleAsset::STATIC_DEPENDS` and therefore already loaded on effectively every page a comment section can appear on — and the enhancement it does is entirely client-driven (scanning `data-*` attributes and fetching embed markup via the same `v-additions` boot as any other legacy enhancer). So oembed embeds inside a comment's richtext output "just work" without any special-casing, precisely because nothing about it depends on a fresh per-request asset registration.
