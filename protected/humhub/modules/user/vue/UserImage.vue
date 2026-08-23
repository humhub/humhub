<template>
    <component
        :is="link ? 'a' : 'span'"
        :href="link ? url : undefined"
        :class="{ 'has-online-status': hasOnlineIndicator, [sizeBucketClass]: hasOnlineIndicator }"
    >
        <img
            class="rounded"
            :style="imageStyle"
            :src="imageUrl"
            :alt="resolvedAlt"
            :data-contentcontainer-id="contentContainerId"
            :data-guid="guid"
        >
        <span
            v-if="hasOnlineIndicator"
            class="tt user-online-status"
            :class="online ? 'user-is-online' : 'user-is-offline'"
            :aria-label="onlineLabel"
            :title="onlineLabel"
        ></span>
    </component>
</template>

<script>
import { i18n } from '@humhub/vue';

/**
 * Renders a user's profile image - `<user-image>`, provided by the user module
 * (`humhub\modules\user\assets\UserVueAsset`) but, like every registered Vue component,
 * available globally in every island by tag name (see
 * docs/develop/ui-js-vuejs-components.md, "Module-provided shared components") - a module
 * outside `user` that nests it (e.g. the comment island below) just needs `UserVueAsset`
 * in its own asset bundle's `$depends`, the same way it already depends on `LikeVueAsset`
 * for `<LikeButton>`. Extracted from `CommentEntry.vue`'s hand-rolled avatar markup, the
 * reference consumer (its own docblock previously carried these parity notes; they now
 * live here since the markup does).
 *
 * Props are modeled on the SERIALIZED author shape
 * (`user\serializers\UserSerializer::short()`) plus a few display options, so a
 * caller normally just spreads it: `<UserImage v-bind="comment.author" />`.
 *
 * ## Parity with `user\widgets\Image::run()` / `BaseImage`
 *
 * - `rounded` class + inline `width`/`height` (from `size`, default 25 -
 *   `Image::run()`'s own default, `width: $this->width . 'px'; height:
 *   $this->height . 'px'`, `$height` defaulting to `$width` -
 *   `BaseImage::init()`).
 * - `data-contentcontainer-id` (from `contentContainerId`) on the `<img>` -
 *   same attribute `Image::run()` sets on `$this->imageOptions`, drives the
 *   user popover card. `data-guid` alongside it is additive (not emitted by
 *   `Image::run()` itself, which never carries a guid - only
 *   `Html::containerLink()`, the separate author-NAME link, does): every
 *   field of the serialized author shape is accepted as a prop so
 *   `v-bind="comment.author"` never produces stray/ignored keys, and a
 *   `data-guid` hook on the avatar itself costs nothing.
 * - The online-status overlay (`.user-online-status` span + `.tt`
 *   tooltip-trigger class + `aria-label`/`title`) renders only when `online`
 *   is non-null (`null` is the server's own "feature disabled or viewer's own
 *   comment" signal - see `UserSerializer::short()`) and
 *   carries the exact `user-is-online`/`user-is-offline` classes and
 *   `UserModule.base`/`Online`|`Offline` label `Image::run()` uses.
 * - `has-online-status` + the size bucket class (`img-size-small` width < 28,
 *   `img-size-large` width > 48, otherwise `img-size-medium` - mirrors
 *   `Image::run()`'s own `$imgSize` thresholds verbatim) are added to
 *   whichever element wraps the image - the `<a>` when `link` is true
 *   (`Image::run()` adds them to `$this->linkOptions` in that case), the root
 *   `<span>` when it is false (`$this->htmlOptions` there) - and ONLY when
 *   the online indicator actually renders, matching `_media.scss`'s own
 *   `.has-online-status`/`.img-size-*` rules, which are meaningless without
 *   an online-status span to size.
 *
 * ## Deliberate deviation: no extra wrapping `<span>`
 *
 * `Image::run()` always wraps its ENTIRE output (bare img/anchor and all) in
 * one more `Html::tag('span', $html, $this->htmlOptions)`, even when `link`
 * is true and `$htmlOptions` is empty - a meaningless, unstyled node in that
 * case (no CSS targets a bare `<span>` around the anchor, and the one real
 * legacy comment view call site only ever passed `data-contentcontainer-id`
 * on it, redundant with the same attribute `Image::run()` already puts on
 * the `<img>` unconditionally). This component's root IS the `<a>`/`<span>`
 * that actually carries `has-online-status`/the size bucket - one fewer
 * pointless DOM node, byte-identical otherwise to what `CommentEntry.vue`
 * rendered by hand before this component existed.
 *
 * The accessible name of the image is built HERE, from the same `base` message
 * `user\widgets\Image::run()` uses (`Profile picture of {displayName}`), rather than
 * being shipped by the API: a localized presentation string has no business in an API
 * payload, and keeping it out is what makes those payloads language-independent. The
 * hosting island therefore has to preload the `base` i18n category (`LikeButton` and
 * `CommentSection` do) — without it the phrase falls back to its English source text,
 * which is still better than the bare display name. An explicit `imageAlt` prop
 * overrides it for callers that have a better phrase.
 *
 * ## Other known deviations from `user\widgets\Image::run()`
 *
 * - **No soft-deleted-user handling.** `Image::run()` forces `$this->link = false` when
 *   `$this->user->status === User::STATUS_SOFT_DELETED`; this component has no notion of user
 *   status at all and always honors `link` as given. A caller rendering a possibly
 *   soft-deleted user must pass `link: false` itself - the user-short serialization
 *   does not currently do this, so this is a real (if narrow) parity gap for that call site,
 *   not just a documentation note.
 * - **No `showTooltip`/`tooltipText` support.** `Image::run()` optionally adds a Bootstrap
 *   tooltip (`data-bs-toggle`/`data-bs-placement`/`data-bs-html`/`data-bs-title`) to the
 *   `<img>` when either option is set; this component never renders those attributes and has
 *   no equivalent props. Not reproduced because no core call site (the comment island) uses
 *   either option today.
 * - **`UserModule.base` i18n category.** `onlineLabel` calls `i18n.t('UserModule.base', ...)`
 *   without preloading that category itself (unlike, e.g., a component with an
 *   `i18nCategories` island-level declaration - see `docs/develop/ui-js-vuejs.md`) - this now
 *   reads naturally (the component ships with the module that owns the category), but the gap
 *   is unchanged in practice: nothing here preloads it either, so a consumer outside the
 *   comment section (where the category is already preloaded page-wide for other reasons)
 *   must still ensure `UserModule.base` is preloaded itself or accept the English fallback
 *   `i18n.t()` returns for an unloaded category.
 */
export default {
    props: {
        guid: { type: String, required: true },
        displayName: { type: String, required: true },
        url: { type: String, required: true },
        imageUrl: { type: String, required: true },
        imageAlt: { type: String, default: null },
        contentContainerId: { type: Number, default: null },
        // Tri-state: null (default) renders no online-status indicator at all -
        // distinct from `false` (renders the "offline" variant).
        online: { type: Boolean, default: null },
        size: { type: Number, default: 25 },
        link: { type: Boolean, default: true },
    },
    computed: {
        resolvedAlt() {
            return this.imageAlt || i18n.t('base', 'Profile picture of {displayName}', { displayName: this.displayName });
        },
        imageStyle() {
            return `width: ${this.size}px; height: ${this.size}px`;
        },
        hasOnlineIndicator() {
            return this.online !== null;
        },
        sizeBucketClass() {
            if (this.size < 28) {
                return 'img-size-small';
            }
            if (this.size > 48) {
                return 'img-size-large';
            }
            return 'img-size-medium';
        },
        onlineLabel() {
            if (this.online === null) {
                return null;
            }
            return this.online ? i18n.t('UserModule.base', 'Online') : i18n.t('UserModule.base', 'Offline');
        },
    },
};
</script>
