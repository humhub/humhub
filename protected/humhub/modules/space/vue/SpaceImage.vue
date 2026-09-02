<template>
    <component :is="link ? 'a' : 'span'" :href="link ? url : undefined">
        <div
            class="space-acronym d-inline-flex justify-content-center align-items-center"
            :class="[acronymIdClass, { 'd-none-space-image': hasImage }]"
            :style="acronymStyle"
            :data-contentcontainer-id="contentContainerId"
        ><span>{{ acronym }}</span></div>
        <img
            v-if="hasImage"
            class="rounded profile-user-photo"
            :class="imageIdClass"
            :style="sizeStyle"
            :src="imageUrl"
            :alt="name"
            :data-contentcontainer-id="contentContainerId"
        >
    </component>
</template>

<script>
/**
 * Renders a space's profile image — `<space-image>`, provided by the space module
 * (`humhub\modules\space\assets\SpaceVueAsset`) and, like every registered Vue component,
 * available in every island by tag name (see
 * docs/develop/ui-js-vuejs-components.md, "Module-provided shared components"). A module
 * nesting it adds `SpaceVueAsset` to its own bundle's `$depends` — the notification island is
 * the reference consumer (a notification's space badge).
 *
 * The Vue analog of `space\widgets\Image`: props are modeled on the serialized space shape
 * (`space\serializers\SpaceSerializer::short()`) plus the display options a caller picks.
 *
 * ## Parity with `space\widgets\Image`
 *
 * A space has either its own profile image or a coloured tile with the initials of its name,
 * and the server-rendered widget emits BOTH, hiding one with `d-none-space-image` (deliberately
 * not `d-none`: changing a space image swaps the two client-side without re-rendering). This
 * component keeps that class contract, including the per-space classes theme CSS and the
 * space-image swap hook onto (`space-profile-acronym-<id>`, `space-profile-image-<id>`), the
 * `rounded profile-user-photo` image classes, the inline size/background/border-radius styles
 * (the widget's `getDynamicStyles()` radius buckets included) and `data-contentcontainer-id`
 * for the popover addition.
 *
 * The acronym itself is derived here, exactly like `Image::getAcronym()`: punctuation stripped,
 * first letter of each word, uppercased, cut to `acronymCount` characters — nothing localized
 * or server-dependent about it, so it needs no API field. When the space has no image
 * (`imageUrl` is `null`, see the serializer's docblock) the `<img>` is not rendered at all
 * instead of pointing at a default image, which is the one deliberate difference: the default
 * space image the widget falls back to only exists to be hidden.
 *
 * @since 1.20
 */
export default {
    props: {
        // Serialized space shape (SpaceSerializer::short()).
        id: { type: [Number, String], default: null },
        name: { type: String, default: '' },
        url: { type: String, default: null },
        color: { type: String, default: null },
        imageUrl: { type: String, default: null },
        contentContainerId: { type: [Number, String], default: null },
        // Display options.
        width: { type: Number, default: 50 },
        height: { type: Number, default: null },
        link: { type: Boolean, default: false },
        acronymCount: { type: Number, default: 2 },
    },
    computed: {
        hasImage() {
            return !!this.imageUrl;
        },
        resolvedHeight() {
            return this.height === null ? this.width : this.height;
        },
        sizeStyle() {
            return { width: this.width + 'px', height: this.resolvedHeight + 'px' };
        },
        acronymStyle() {
            return {
                ...this.sizeStyle,
                // Same fallback the widget uses when a space has no colour of its own.
                backgroundColor: this.color || 'var(--background3)',
                borderRadius: this.borderRadius + 'px',
            };
        },
        // Mirrors Image::getDynamicStyles()'s width buckets.
        borderRadius() {
            if (this.width < 35) {
                return 2;
            }
            if (this.width < 140 && this.width > 40) {
                return 3;
            }

            return 4;
        },
        acronymIdClass() {
            return this.id === null ? null : 'space-profile-acronym-' + this.id;
        },
        imageIdClass() {
            return this.id === null ? null : 'space-profile-image-' + this.id;
        },
        acronym() {
            const words = String(this.name || '')
                // Keep letters, digits and whitespace - the same character class as
                // Image::getAcronym()'s `[^\p{L}\d\s]` strip.
                .replace(/[^\p{L}\d\s]+/gu, '')
                .split(/\s+/)
                .filter((word) => word.length > 0);

            return words
                .map((word) => word.slice(0, 1))
                .join('')
                .toUpperCase()
                .slice(0, this.acronymCount);
        },
    },
};
</script>
