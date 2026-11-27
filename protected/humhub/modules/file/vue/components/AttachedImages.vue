<template>
    <div class="post-files-images">
        <div
            v-for="(row, rowIndex) in layout.rows"
            :key="rowIndex"
            class="post-files-images-row"
            :style="{ height: px(row.height) }"
        >
            <a
                v-for="item in row.items"
                :key="images[item.index].guid"
                class="post-files-image"
                :style="{ width: px(item.width) }"
                :data-ui-gallery="galleryId"
                :href="images[item.index].url"
                :data-ui-gallery-src="images[item.index].displayUrl || null"
                :title="images[item.index].fileName"
                :aria-label="isMoreTile(item.index) ? moreLabel(images[item.index]) : null"
            >
                <img
                    loading="lazy"
                    decoding="async"
                    :src="source(images[item.index], item.width)"
                    :alt="images[item.index].fileName"
                    :width="Math.round(item.width)"
                    :height="Math.round(row.height)"
                >
                <span v-if="isMoreTile(item.index)" class="post-files-image-more" aria-hidden="true">+{{ layout.hidden }}</span>
            </a>
        </div>
        <a
            v-for="file in hiddenImages"
            :key="file.guid"
            hidden
            :data-ui-gallery="galleryId"
            :href="file.url"
            :data-ui-gallery-src="file.displayUrl || null"
            :title="file.fileName"
        >{{ file.fileName }}</a>
    </div>
</template>

<script>
/**
 * The images of `AttachedFiles.vue` in justified rows (`justify.js`): at most two rows, the last tile says how many
 * follow, the images after it stay in the media viewer as hidden links. A component of its own, so a resize
 * re-renders the rows only.
 *
 * @since 1.20
 */
import { i18n } from '@humhub/vue';
import { justify } from '../justify.js';

// At most this many rows, the last tile says how many follow
const MAX_ROWS = 2;
// In rem: the sizes where the CSS variables of _file.scss are not set, and the width before the area is measured
const SIZES = { rowHeight: 12, singleHeight: 20, gap: 0.5, width: 36 };
// Long side of the preview image: assumes the file module's default imagePreviewMaxWidth / imagePreviewMaxHeight,
// which are configurable on the server
const PREVIEW_SIZE = 400;

// One ResizeObserver for all image areas of the page. A new width is laid out in the next frame, for all areas in
// the same one: the new rows change the height of the area, which notifies the observer once more.
let observer = null;
let frame = 0;
// The last width and the layout callback of each observed area, and the areas whose width changed since the last frame
const areas = new WeakMap();
const changed = new Set();

const rootFontSize = () => parseFloat(getComputedStyle(document.documentElement).fontSize) || 16;

function layoutChanged() {
    frame = 0;
    const rem = rootFontSize();
    changed.forEach((el) => {
        const area = areas.get(el);
        area.resize(area.width, rem);
    });
    changed.clear();
}

function resized(entries) {
    entries.forEach(({ target, contentRect }) => {
        const area = areas.get(target);
        // Rounded as `clientWidth`, the width measured on mount
        const width = Math.round(contentRect.width);
        // A change of height only (the new rows) costs nothing, a hidden area keeps its rows
        if (area && width > 0 && width !== area.width) {
            area.width = width;
            changed.add(target);
        }
    });
    if (changed.size > 0 && !frame) {
        frame = requestAnimationFrame(layoutChanged);
    }
}

function observe(el, width, resize) {
    if (typeof ResizeObserver === 'undefined') {
        return;
    }
    observer ??= new ResizeObserver(resized);
    areas.set(el, { width, resize });
    observer.observe(el);
}

function unobserve(el) {
    observer?.unobserve(el);
    areas.delete(el);
    changed.delete(el);
}

const ratioOf = (file) => (file.width > 0 && file.height > 0 ? file.width / file.height : 1);

export default {
    name: 'AttachedImages',
    props: {
        // The files with a preview, see AttachedFiles.vue
        images: { type: Array, required: true },
        // The record's group in the media viewer
        galleryId: { type: String, required: true },
    },
    data() {
        // `box`: width and sizes of the area in px, once measured
        return { box: null };
    },
    computed: {
        layout() {
            const box = this.box || {
                width: SIZES.width * 16,
                rowHeight: SIZES.rowHeight * 16,
                singleHeight: SIZES.singleHeight * 16,
                gap: SIZES.gap * 16,
            };

            return justify(this.images.map(ratioOf), { ...box, maxRows: MAX_ROWS });
        },
        shownCount() {
            return this.layout.rows.reduce((count, row) => count + row.items.length, 0);
        },
        hiddenImages() {
            return this.images.slice(this.shownCount);
        },
    },
    created() {
        // The guids of the images showing the display version, deliberately not reactive
        this.displayed = new Set();
    },
    mounted() {
        // Measured at once, so the rows never show at the fallback width
        const width = this.$el.clientWidth;
        if (width > 0) {
            this.measure(width, rootFontSize());
        }
        observe(this.$el, width, this.measure);
    },
    beforeUnmount() {
        unobserve(this.$el);
    },
    methods: {
        // The width and the sizes of _file.scss (CSS variables in rem or px), in px
        measure(width, rem) {
            const style = getComputedStyle(this.$el);
            const size = (name, fallback) => {
                const value = style.getPropertyValue(name);
                const number = parseFloat(value);
                if (Number.isNaN(number)) {
                    return fallback * rem;
                }

                return value.trim().endsWith('px') ? number : number * rem;
            };

            this.box = {
                width,
                rowHeight: size('--hh-attached-images-row-height', SIZES.rowHeight),
                singleHeight: size('--hh-attached-images-single-height', SIZES.singleHeight),
                gap: size('--hh-attached-images-gap', SIZES.gap),
            };
        },
        px(value) {
            return `${value}px`;
        },
        isMoreTile(index) {
            return this.layout.hidden > 0 && index === this.shownCount - 1;
        },
        moreLabel(file) {
            return i18n.t('FileModule.base', '{fileName}, {count,plural,=1{one more image} other{# more images}}', {
                fileName: file.fileName,
                count: this.layout.hidden,
            });
        },
        // The preview fits into PREVIEW_SIZE², a larger tile (or a sharper screen) gets the display version and keeps
        // it while the area shrinks. An original that fits into the preview is the preview.
        source(file, width) {
            if (this.displayed.has(file.guid)) {
                return file.displayUrl;
            }

            const fits = file.width > 0 && file.height > 0 && file.width <= PREVIEW_SIZE && file.height <= PREVIEW_SIZE;
            if (!file.displayUrl || fits) {
                return file.previewUrl;
            }

            const previewWidth = Math.min(PREVIEW_SIZE, PREVIEW_SIZE * ratioOf(file));
            if (width * (window.devicePixelRatio || 1) <= previewWidth * 1.25) {
                return file.previewUrl;
            }

            // Not before the area is measured: the fallback width is never shown
            if (this.box) {
                this.displayed.add(file.guid);
            }

            return file.displayUrl;
        },
    },
};
</script>
