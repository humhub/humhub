<template>
    <div
        class="c-drop-zone"
        @dragenter.capture="onEnter"
        @dragleave.capture="onLeave"
        @dragover="onOver"
        @drop.capture="reset"
        @drop="onDrop"
    >
        <slot></slot>
        <Transition name="c-drop-zone-fade">
            <div v-if="active" class="c-drop-zone__overlay" :class="{ 'is-refused': !accept }" aria-hidden="true">
                <span class="c-drop-zone__label">
                    <i class="ti" :class="accept ? 'ti-upload' : 'ti-ban'"></i>{{ text }}
                </span>
            </div>
        </Transition>
    </div>
</template>

<script>
import { i18n } from '@humhub/vue';

const carriesFiles = (event) => Array.prototype.includes.call(event.dataTransfer?.types || [], 'Files');

/**
 * A drop target for files from the desktop (`<drop-zone>`) around any content: while files are
 * dragged over it an overlay covers the content with a label, and dropping emits the files.
 * Drags that carry no files (text, an element of the page) are ignored.
 *
 * - `accept` (default `true`): `false` shows the overlay in its refused state (`ti-ban`,
 *   `refusedLabel`) and emits nothing on a drop — e.g. for a reader without write access.
 * - `label` / `refusedLabel`: the overlay's text (platform defaults otherwise).
 * - Emits `drop(files, event)` — `files` the `FileList` of the drop.
 * - Nested targets: `dragenter`/`dragleave` are counted in the capture phase, so moving between
 *   children does not flicker. A child that handles a drop itself (a folder tile taking an
 *   upload) calls `event.stopPropagation()`; the overlay still clears (capture phase) and the
 *   zone emits nothing. A `dragleave` whose `relatedTarget` lands outside the zone resets the
 *   count outright, so a hovered child removed mid-drag (its own `dragleave` never fires) can't
 *   leave the overlay stuck.
 * - A child accepting file drops (a folder tile) must sit in an accepting zone: the zone's
 *   `dragover` runs after the child's and sets `dropEffect` from `accept`.
 * - Styling: `.c-drop-zone` in `resources/scss/_item-browser.scss`.
 *
 * @since 1.20
 */
export default {
    name: 'DropZone',
    props: {
        accept: { type: Boolean, default: true },
        label: { type: String, default: null },
        refusedLabel: { type: String, default: null },
    },
    emits: ['drop'],
    data() {
        return { depth: 0 };
    },
    computed: {
        active() {
            return this.depth > 0;
        },
        text() {
            return this.accept
                ? (this.label || i18n.t('base', 'Drop files here to upload them'))
                : (this.refusedLabel || i18n.t('base', 'Files cannot be uploaded here'));
        },
    },
    methods: {
        onEnter(event) {
            if (carriesFiles(event)) {
                this.depth++;
            }
        },
        onLeave(event) {
            if (!carriesFiles(event) || this.depth === 0) {
                return;
            }
            const to = event.relatedTarget;
            // Leaving to an element outside the zone ends the drag here, whatever the count says
            // (a hovered child removed mid-drag never sends its dragleave).
            this.depth = (to instanceof Node && !this.$el.contains(to)) ? 0 : this.depth - 1;
        },
        onOver(event) {
            if (!carriesFiles(event)) {
                return;
            }
            event.preventDefault();
            if (event.dataTransfer) {
                event.dataTransfer.dropEffect = this.accept ? 'copy' : 'none';
            }
        },
        reset() {
            this.depth = 0;
        },
        onDrop(event) {
            if (!carriesFiles(event)) {
                return;
            }
            event.preventDefault();
            if (this.accept && event.dataTransfer.files.length) {
                this.$emit('drop', event.dataTransfer.files, event);
            }
        },
    },
};
</script>
