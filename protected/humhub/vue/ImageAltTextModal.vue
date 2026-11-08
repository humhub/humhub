<template>
    <UiModal
        v-model:show="show"
        :title="titleLabel"
        :backdrop-close="!required"
        :keyboard="!required"
        @opened="focusInput"
        @closed="onClosed"
    >
        <!-- Required: no close button, the image is described or removed -->
        <template v-if="required" #header="{ titleId }">
            <h5 class="modal-title" :id="titleId">{{ titleLabel }}</h5>
        </template>

        <img v-if="src" :src="src" alt="" class="image-alt-text-preview">
        <HumHubForm ref="form" model-name="ImageAltText" @submit="save">
            <TextField
                ref="field"
                attribute="alt"
                :label="altLabel"
                :hint="hintLabel"
                :required="required"
                v-model="alt"
            />
        </HumHubForm>

        <template #footer>
            <button type="button" class="btn btn-light" @click="show = false">{{ required ? removeLabel : skipLabel }}</button>
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
 * With `required` the text cannot be empty and the dialog cannot be dismissed: "Remove image"
 * replaces Skip, and `altText:cancel` means the image is not wanted.
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
        required: { type: Boolean, default: false },
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
            return this.required
                ? i18n.t('ContentModule.richtexteditor', 'Describe the image for people who cannot see it.')
                : i18n.t('ContentModule.richtexteditor', 'Describe the image for people who cannot see it. Leave it empty if the image is purely decorative.');
        },
        requiredMessage() {
            return i18n.t('ContentModule.richtexteditor', 'Please describe the image.');
        },
        skipLabel() {
            return i18n.t('ContentModule.richtexteditor', 'Skip');
        },
        removeLabel() {
            return i18n.t('ContentModule.richtexteditor', 'Remove image');
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
            if (this.required && this.alt.trim() === '') {
                this.$refs.form.setErrors({ alt: [this.requiredMessage] });
                this.focusInput();
                return;
            }
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
