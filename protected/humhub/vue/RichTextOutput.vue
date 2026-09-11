<template>
    <div v-if="message" v-additions>
        <div :key="envelopeKey" v-bind="envelopeAttrs">{{ message }}</div>
        <div v-if="hasOembeds" :key="renderOptionsKey" class="richtext-oembed-container" style="display:none">
            <div v-for="(html, url) in oembeds" :key="url" :data-oembed="escapeOembedUrl(url)" v-html="html"></div>
        </div>
    </div>
</template>

<script>
/**
 * Renders a rich text message inside a Vue island, reconstructing client-side the exact
 * envelope `RichText::output()` used to build server-side - generic core interop component
 * (any module's island can embed it wherever it would otherwise render a richtext message;
 * the comment section, `CommentEntry.vue`, is the reference consumer).
 *
 * `message` is the RAW, PROCESSED MARKDOWN text (`RichText::outputMarkdownAndRenderOptions()`'s
 * `markdown` - already run through every extension's `onBeforeOutput()`, e.g. mention
 * resolution, but crucially NOT HTML-encoded) - it is bound via `{{ message }}` text
 * interpolation, never `v-html`, so it always lands as an escaped DOM TEXT node: a literal
 * `<script>` in a message renders as inert text, exactly as `Html::encode($output)` +
 * `parent::run()` used to guarantee server-side (see `ProsemirrorRichText::run()`), just
 * enforced client-side instead. `v-additions` (below) boots the legacy richtext DISPLAY
 * addition on the resulting envelope div, which reads that same text content and renders it
 * to HTML via markdown-it - the actual markdown -> HTML step has always been client-side, this
 * only removes the server's now-redundant HTML-encode-and-wrap step from the wire payload.
 *
 * `renderOptions` mirrors `ProsemirrorRichText::getMarkdownAndRenderOptions()`'s `options`
 * bucket - the SAME `data-*` attributes (`exclude`/`include`/`plugin-options`/`preset`/`edit`/
 * `ui-richtext`/`ui-widget`/`ui-init`) `RichText::output()`'s envelope div carried via
 * `AbstractRichText::getData()` + `JsWidget::setDefaultOptions()`, applied to the envelope
 * generically (`v-bind="envelopeAttrs"`) so this component never needs updating when a new
 * plugin option is added server-side. Every key becomes `data-<key>`; `true`/`false`/array
 * values are normalized to the same wire representation Yii's `Html::renderTagAttributes()`
 * already used server-side (`true` -> valueless attribute, `false` -> omitted entirely,
 * array/object -> a JSON-encoded string) - see `envelopeAttrs` below. The envelope's own
 * auto-generated widget `id` is deliberately never reconstructed: it was always a meaningless
 * per-render DOM-uniqueness counter (nothing client or theme-CSS-side ever read it - the
 * `[data-ui-richtext]` selector, not an id, is what locates richtext content).
 *
 * **`oembeds` - the one trusted `v-html` in this component.** `OembedExtension` is the one
 * richtext extension that genuinely needs server participation (a third-party HTTP fetch a
 * client cannot perform for itself) - see `docs/develop/ui-js-vuejs-interop.md`, "RichTextOutput".
 * Its server-fetched preview fragments travel in `renderOptions.oembeds` (a plain `{url:
 * html}` map, the SAME trusted, server-built markup `OembedExtension::buildOembedOutput()`
 * used to append directly into the old HTML envelope string) and are rendered here via
 * `v-html`, rebuilding the exact same hidden `.richtext-oembed-container` sibling structure -
 * trusted because it is server-controlled HTML from `UrlOembed::getOEmbed()`, never raw user
 * input (unlike `message`, which is ALWAYS escaped text, never `v-html`, precisely because it
 * IS user input). `oembeds` itself is excluded from `envelopeAttrs` (it is not a `data-*`
 * attribute, just the one nested config value with its own dedicated rendering).
 *
 * Deliberately classless beyond what `renderOptions`/`oembeds` dictate: the caller owns
 * layout and styling of the rendered output via normal Vue attribute fallthrough onto this
 * component's own root (`class`, `data-ui-markdown`, `data-ui-show-more`, ... - see
 * `CommentEntry.vue`), landing one level ABOVE the envelope div, exactly as today.
 *
 * **`:key`-forced remount on content change.** The envelope div is otherwise a PERSISTENT
 * element across a `message`/`renderOptions` update: Vue's patcher has no reason to replace a
 * same-position, same-tag child, so an in-place update would normally just swap its text
 * content and re-run `v-bind="envelopeAttrs"` on the SAME DOM node. That silently breaks the
 * legacy richtext DISPLAY addition `v-additions` boots on it: once booted, the addition caches
 * its widget instance in jQuery `.data()` on that exact node (see
 * `docs/develop/ui-js-vuejs-interop.md`, "RichTextOutput"), and legacy widget init is a
 * once-per-node guard - re-running `v-additions` against an already-initialized node is a
 * no-op, so a genuinely new markdown string would never get re-rendered to HTML; the user
 * would see the raw markdown text `{{ message }}` just wrote into the DOM. `envelopeKey`
 * (below) - a cheap, deterministic string combining `message` and the serialized
 * `renderOptions` - forces Vue to unmount the OLD envelope node and mount a genuinely NEW one
 * on any real change, so the addition's jQuery-data cache can never survive onto content it
 * never actually processed. Same reasoning for the oembed container's own `:key`
 * (`renderOptionsKey`): keyed so a `renderOptions.oembeds` change (a different set of preview
 * fragments) remounts the container instead of patching in place - the per-url `:key="url"` on
 * each fragment `div` already handles fine-grained add/remove within it either way, this is
 * belt-and-suspenders for the container itself.
 */

// Mirrors humhub.util.js's own `entityMap` for the `escapeHtml(value, true)` ("simple") variant -
// see `escapeOembedUrl()` below.
const OEMBED_URL_ENTITY_MAP = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };

export default {
    props: {
        message: { type: String, default: null },
        renderOptions: { type: Object, default: () => ({}) },
    },
    computed: {
        envelopeAttrs() {
            const attrs = {};

            Object.entries(this.renderOptions || {}).forEach(([key, value]) => {
                if (key === 'oembeds' || value === false || value === null || value === undefined) {
                    return;
                }

                if (value === true) {
                    attrs['data-' + key] = '';
                    return;
                }

                attrs['data-' + key] = (typeof value === 'object') ? JSON.stringify(value) : value;
            });

            return attrs;
        },
        oembeds() {
            return (this.renderOptions && this.renderOptions.oembeds) || {};
        },
        hasOembeds() {
            return Object.keys(this.oembeds).length > 0;
        },
        /**
         * Serialized `renderOptions`, reused as (part of) the `:key`s described in the class
         * docblock's "`:key`-forced remount on content change" section above.
         */
        renderOptionsKey() {
            return JSON.stringify(this.renderOptions || {});
        },
        /**
         * @see the class docblock's "`:key`-forced remount on content change" section above.
         * NUL-separated rather than plain concatenation: `message` is free-form user text, and
         * a plain join could otherwise collide across the message/renderOptions boundary (two
         * different (message, renderOptions) pairs producing the same joined string). A NUL
         * byte cannot occur in `message` (always a JSON string round-tripped from the server).
         */
        envelopeKey() {
            return this.message + '\u0000' + this.renderOptionsKey;
        },
    },
    methods: {
        /**
         * Mirrors `util.string.escapeHtml(value, true)` in
         * `protected/humhub/resources/js/humhub/humhub.util.js` byte-for-byte (its "simple"
         * variant - second arg `true` - which escapes only `& < > " '`, leaving backtick/`=`/`/`
         * alone). `humhub.oembed.js`'s `findSnippetByUrl()` locates this fragment by querying
         * `[data-oembed="' + $.escapeSelector(util.string.escapeHtml(url, true)) + '"]` - so the
         * `data-oembed` attribute rendered here MUST equal that exact escaped string, not the
         * raw url, or the lookup silently fails for any url containing one of those five
         * characters (a `&` in a query string being the common case) and the embed degrades to
         * a plain link with no live preview/lazy-load behavior. Kept as a tiny local function -
         * rather than reaching into `@humhub/vue`/`humhub.modules.util` - because it is a pure,
         * dependency-free string transform and no sibling island component reaches into legacy
         * modules directly either.
         */
        escapeOembedUrl(url) {
            return String(url).replace(/[&<>"']/g, (char) => OEMBED_URL_ENTITY_MAP[char]);
        },
    },
};
</script>
