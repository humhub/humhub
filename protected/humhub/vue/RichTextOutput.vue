<template>
    <div v-if="message && ready" v-additions>
        <div :key="message" v-bind="ENVELOPE_ATTRS">{{ message }}</div>
    </div>
</template>

<script>
/**
 * Renders a rich text message inside a Vue island - the client-side counterpart of
 * `RichText::output()`. Any module's island can embed it wherever it would otherwise render a
 * richtext message; the comment section (`CommentEntry.vue`) is the reference consumer.
 *
 * `message` is the processed markdown an API payload ships (`RichText::outputMarkdown()`:
 * mentions resolved, legacy syntax rewritten, NOT HTML-encoded). It is bound via `{{ message }}`
 * text interpolation, never `v-html`, so it always lands as an escaped DOM text node - a literal
 * `<script>` in a message renders as inert text, exactly what `Html::encode()` guaranteed for
 * the server-rendered envelope. `v-additions` then boots the legacy richtext DISPLAY widget
 * (`humhub.ui.richtext.prosemirror.js`, addressed by the envelope's `data-ui-widget`) on the
 * envelope div, which reads that text and renders the markdown to HTML via markdown-it - the
 * markdown -> HTML step has always been client-side.
 *
 * **Oembed previews.** The one thing the display widget cannot derive from the text: an
 * `[url](oembed:url)` link renders as the preview `humhub.oembed.js` `get(url)` knows, or as a
 * plain link if it knows none. A server-rendered richtext ships the previews as a hidden sibling
 * of the envelope; here they are fetched first (`oembed.load()`, honoring the viewer's consent
 * for domains they have not allowed) and the envelope renders once they are cached - so a
 * message with oembed links appears a request later, a message without renders at once.
 *
 * **`:key`-forced remount.** The display widget caches its instance on the envelope node
 * (jQuery `.data()`) and initializes once per node, so an in-place text swap on the same node
 * would never be re-rendered - the user would see raw markdown. Keying the envelope by
 * `message` makes Vue mount a new node for a new message instead.
 *
 * Deliberately classless: the caller owns layout and styling via attribute fallthrough onto the
 * root (`class`, `data-ui-markdown`, `data-ui-show-more`, ... - see `CommentEntry.vue`), one
 * level above the envelope, exactly where they land around a server-rendered richtext.
 */
import { log, oembed } from '@humhub/vue';

// The envelope `RichText::output()` renders (`AbstractRichText::getData()` +
// `JsWidget::setDefaultOptions()`), reduced to what the display widget reads: `data-ui-widget` +
// `data-ui-init` boot it, `data-ui-richtext` is what theme CSS and other modules select richtext
// content by. Plugin selection (`data-preset`/`data-exclude`/`data-include`) is the widget's
// default - the platform preset for content output.
const ENVELOPE_ATTRS = {
    'data-ui-richtext': '',
    'data-ui-widget': 'ui.richtext.prosemirror.RichText',
    'data-ui-init': '',
};

// `[text](oembed:<url>)` - the link `OembedExtension` writes, see `RichTextLinkExtension::getRegex()`.
const OEMBED_LINK = /\]\(oembed:([^)\s]+)/g;

// As many previews as a server-rendered richtext ships (`OembedExtension::$maxOembed`); links
// beyond that render as plain links.
const MAX_OEMBEDS = 10;

const oembedUrls = (message) => {
    const urls = [];

    for (const match of String(message || '').matchAll(OEMBED_LINK)) {
        if (!urls.includes(match[1])) {
            urls.push(match[1]);
        }
        if (urls.length === MAX_OEMBEDS) {
            break;
        }
    }

    return urls;
};

export default {
    props: {
        message: { type: String, default: null },
    },
    data() {
        return {
            ENVELOPE_ATTRS,
            // False while the previews of the current message are being loaded.
            ready: false,
        };
    },
    watch: {
        message: { immediate: true, handler: 'prepare' },
    },
    methods: {
        prepare(message) {
            const urls = oembedUrls(message);

            if (!urls.length) {
                this.ready = true;
                return;
            }

            this.ready = false;

            const done = () => {
                // A message that changed meanwhile has a load of its own; this one is stale.
                if (this.message === message) {
                    this.ready = true;
                }
            };

            oembed.load(urls, { consent: true, silent: true }).then(done, (error) => {
                log.warn('RichTextOutput: could not load oembed previews, rendering plain links', error);
                done();
            });
        },
    },
};
</script>
