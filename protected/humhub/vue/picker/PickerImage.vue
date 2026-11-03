<template>
    <i v-if="kind === 'icon'" :class="iconClass" aria-hidden="true"></i>
    <span v-else-if="kind === 'color'" class="picker-color" :style="{ backgroundColor: image.color }" aria-hidden="true"></span>
    <img v-else-if="kind === 'url'" :src="image.url" :width="size" :height="size" class="rounded" alt="">
    <component :is="registered.component" v-else-if="kind === 'registered'" v-bind="registered.toProps(image, size)" />
</template>

<script>
import { getPickerImage } from '@humhub/vue';

/**
 * Renders a picker image description (see docs/develop/ui-js-vuejs-picker.md, "Images"):
 * `icon`, `color` and `url` itself, any other type through the component registered with
 * `registerPickerImage()` (by its registered name — never imported, so module components stay
 * in their bundles). No image, or an unregistered type, renders nothing.
 *
 * @since 1.20
 */
export default {
    name: 'PickerImage',
    props: {
        image: { type: Object, default: null },
        size: { type: Number, default: 24 },
    },
    computed: {
        registered() {
            return this.image ? getPickerImage(this.image.type) : null;
        },
        kind() {
            if (!this.image) {
                return null;
            }
            if (['icon', 'color', 'url'].includes(this.image.type)) {
                return this.image.type;
            }
            return this.registered ? 'registered' : null;
        },
        iconClass() {
            const name = String(this.image.name || '');
            return name.startsWith('fa-') ? ['fa', name] : ['ti', `ti-${name}`];
        },
    },
};
</script>
