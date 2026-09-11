<template>
    <!-- eslint-disable-next-line vue/no-v-html -- server-rendered image or icon, see docblock -->
    <span v-if="imageHtml" class="current-space" v-html="imageHtml"></span>

    <div v-else class="no-space">
        <!-- eslint-disable-next-line vue/no-v-html -- server-rendered icon, see docblock -->
        <span v-html="noSpaceIconHtml"></span>
        <br>
        {{ noSpaceLabel }}
    </div>
</template>

<script>
/**
 * What the space menu's button shows: the current space's image, or the "My spaces" placeholder
 * outside a space.
 *
 * Its own island rather than part of `SpaceChooser`, because the two sit in different places:
 * the button is a direct child of the menu item (`.nav > li.nav-item > a.nav-link` — a topbar
 * rule that an element in between would break), the menu is the dropdown beside it. Mounting
 * inside the anchor keeps that structure intact.
 *
 * ## Why it is an island at all
 *
 * Server-rendered would be enough for a full page load — but a pjax navigation does not
 * re-render the top menu, so the button has to follow along when the visitor moves into or out
 * of a space. That is what the legacy chooser's `setSpace()`/`setNoSpace()` did on
 * `humhub:space:changed` and `humhub:ready`, and this island does the same with the same
 * events. The image travels as rendered markup because it is the server's `space\widgets\Image`
 * (profile image or coloured acronym tile), the same markup `humhub:space:changed` has always
 * carried.
 *
 * @since 1.20
 */
import { events, i18n } from '@humhub/vue';

const SPACE_CHANGED = 'humhub:space:changed';
const READY = 'humhub:ready';

export default {
    i18nCategories: ['SpaceModule.chooser'],
    props: {
        // Rendered image of the space currently shown, empty outside a space.
        initialImageHtml: { type: String, default: '' },
        // Rendered icon of the "My spaces" state.
        noSpaceIconHtml: { type: String, default: '' },
    },
    data() {
        return {
            imageHtml: this.initialImageHtml,
        };
    },
    computed: {
        noSpaceLabel() {
            return i18n.t('SpaceModule.chooser', 'My spaces');
        },
    },
    mounted() {
        events.on(SPACE_CHANGED, this.onSpaceChanged);
        events.on(READY, this.onReady);
    },
    beforeUnmount() {
        events.off(SPACE_CHANGED, this.onSpaceChanged);
        events.off(READY, this.onReady);
    },
    methods: {
        onSpaceChanged(event, space) {
            this.imageHtml = (space && space.image) || '';
        },
        /**
         * A pjax navigation that leaves the space section behind: the platform's own `space`
         * module knows whether the page shown is a space page.
         */
        onReady() {
            const space = window.humhub && window.humhub.modules && window.humhub.modules.space;

            if (space && typeof space.isSpacePage === 'function' && !space.isSpacePage()) {
                this.imageHtml = '';
            }
        },
    },
};
</script>
