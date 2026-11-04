<template>
    <UiModal v-model:show="show" :title="titleLabel" @opened="focusInput" @closed="onClosed">
        <img v-if="src" :src="src" alt="" class="image-alt-text-preview">
        <HumHubForm model-name="ImageAltText" @submit="save">
            <TextField
                ref="field"
                attribute="alt"
                :label="altLabel"
                :hint="hintLabel"
                v-model="alt"
            />
        </HumHubForm>

        <template #footer>
            <button type="button" class="btn btn-light" @click="show = false">{{ skipLabel }}</button>
            <button type="button" class="btn btn-primary" @click="save">{{ saveLabel }}</button>
        </template>
    </UiModal>
</template>

<script>
/**
 * Asks for the alternative text of an image the richtext editor has just inserted
 * (`plugins/image/alt-text.js` of the editor mounts it through `humhub.vue.mountElement()`).
 *
 * Opens on mount. Its result bubbles from the island to the mount point as a DOM event,
 * since the mount point's props are JSON and carry no callbacks:
 *
 * - `altText:save` with `detail: {alt}` (trimmed; empty marks the image as decorative)
 * - `altText:cancel` when dismissed (Skip, ×, Escape, backdrop)
 *
 * The mount point is the caller's to remove afterwards, which unmounts the island.
 *
 * @since 1.20
 */
import { i18n } from '@humhub/vue';
import UiModal from './UiModal.vue';
import HumHubForm from './HumHubForm.vue';
import TextField from './TextField.vue';

export default {
    name: 'ImageAltTextModal',
    components: { UiModal, HumHubForm, TextField },
    i18nCategories: ['ContentModule.richtexteditor', 'base'],
    props: {
        src: { type: String, default: null },
    },
    data() {
        return {
            show: false,
            alt: '',
            saved: false,
        };
    },
    computed: {
        titleLabel() {
            return i18n.t('ContentModule.richtexteditor', 'Describe the image');
        },
        altLabel() {
            return i18n.t('ContentModule.richtexteditor', 'Alternative text');
        },
        hintLabel() {
            return i18n.t('ContentModule.richtexteditor', 'Describe the image for people who cannot see it. Leave it empty if the image is purely decorative.');
        },
        skipLabel() {
            return i18n.t('ContentModule.richtexteditor', 'Skip');
        },
        saveLabel() {
            return i18n.t('base', 'Save');
        },
    },
    mounted() {
        this.show = true;
    },
    methods: {
        focusInput() {
            this.$refs.field?.$refs.input?.focus();
        },
        save() {
            this.saved = true;
            this.show = false;
        },
        onClosed() {
            // `$el` is the Teleport's anchor inside the mount point: the event bubbles to it
            const event = this.saved
                ? new CustomEvent('altText:save', { bubbles: true, detail: { alt: this.alt.trim() } })
                : new CustomEvent('altText:cancel', { bubbles: true });
            this.$el.dispatchEvent(event);
        },
    },
};
</script>
