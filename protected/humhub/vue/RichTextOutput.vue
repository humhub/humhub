<template>
    <div v-if="output" v-html="output" v-additions></div>
</template>

<script>
/**
 * Renders a server-generated RichText envelope inside a Vue island. Generic
 * core interop component — any module's island can embed it wherever it
 * would otherwise `v-html` a `RichText::output()` string (the comment
 * section is the reference consumer, see CommentEntry.vue).
 *
 * `output` MUST be trusted, server-generated HTML produced by
 * `RichText::output()` (see AbstractRichTextEditor::editOutput()) — NEVER
 * raw/untrusted user input. It is bound with `v-html`, and the envelope's
 * markdown source (carried in `data-*` attributes inside it) is rendered to
 * HTML entirely client-side by the legacy richtext addition, exactly as it
 * is today for server-rendered content.
 *
 * `v-additions` (see humhub.vue.js) boots that addition — and any other
 * legacy enhancer targeting this subtree (timeago, mentions, oembed, ...) —
 * on mount, and re-runs it on update so a re-rendered envelope (e.g. after
 * editing the content) is picked up again.
 *
 * Deliberately classless: the caller owns layout and styling of the
 * rendered output.
 */
export default {
    props: {
        output: { type: String, default: null },
    },
};
</script>
