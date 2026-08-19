<template>
    <LegacyFormWrapper ref="wrapper" :shell-html="shellHtml" />
</template>

<script>
/**
 * Hosts a comment create/reply form: LegacyFormWrapper renders the server
 * shell (richtext editor + upload widget), this component only owns
 * intercepting its native submit.
 *
 * Submitting is a P2-5 stub for this task: the shell is a real
 * `<form action="/comment/comment/post" method="post">` with a native
 * `<button type="submit">` (see comment/widgets/views/form.php), so without
 * interception a click would trigger a full browser POST/navigation. We
 * capture the form's own 'submit' event (covers both the button and any
 * future implicit submit) and preventDefault it instead, logging a TODO -
 * the actual create/update request, 422 field-error handling, clearing the
 * editor and bumping counts all land in P2-5.
 *
 * Known parity gap: `shellHtml` is a single shared template per section (one
 * `formShellHtml` prop on CommentSection), so a reply form clones the exact
 * same placeholder text as the main form ("Write a new comment..." vs "...
 * reply...") - the legacy per-context placeholder is baked in at
 * server-render time and this task doesn't introduce a second shell variant.
 */
import { log } from '@humhub/vue';
import LegacyFormWrapper from './LegacyFormWrapper.vue';

export default {
    components: { LegacyFormWrapper },
    props: {
        shellHtml: { type: String, required: true },
        contentId: { type: Number, required: true },
        parentCommentId: { type: Number, default: null },
    },
    emits: ['submit'],
    mounted() {
        this.formEl = this.$refs.wrapper.$el.querySelector('form');
        if (this.formEl) {
            this.formEl.addEventListener('submit', this.onSubmit);
        }
    },
    beforeUnmount() {
        if (this.formEl) {
            this.formEl.removeEventListener('submit', this.onSubmit);
        }
    },
    methods: {
        onSubmit(event) {
            event.preventDefault();

            // TODO(P2-5): POST /comment/comment/create with
            // {message: this.$refs.wrapper.getValue(), fileList: this.$refs.wrapper.getFileGuids(),
            // contentId: this.contentId, parentCommentId: this.parentCommentId}; handle 422
            // {errors} by re-rendering field errors, on success clear()+append the returned
            // comment and bump CommentSection's total.
            log.warn('Comment submit is not implemented yet (P2-5)', {
                contentId: this.contentId,
                parentCommentId: this.parentCommentId,
            });

            this.$emit('submit', { contentId: this.contentId, parentCommentId: this.parentCommentId });
        },
        /** Proxies to the wrapper so callers (reply toggle, section toggle) don't touch jQuery/legacy widgets. */
        focus() {
            if (this.$refs.wrapper) {
                this.$refs.wrapper.focus();
            }
        },
    },
};
</script>
