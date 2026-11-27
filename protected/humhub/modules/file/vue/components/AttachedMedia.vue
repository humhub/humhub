<template>
    <div class="post-files-images">
        <div
            v-for="(row, rowIndex) in layout.rows"
            :key="rowIndex"
            class="post-files-images-row"
            :style="{ height: px(row.height) }"
        >
            <template v-for="item in row.items" :key="media[item.index].guid">
                <a
                    v-if="isVideo(media[item.index])"
                    class="post-files-tile post-files-video"
                    :class="{ 'is-unplayable': failed[media[item.index].guid] }"
                    :style="{ width: px(item.width) }"
                    :data-ui-gallery="galleryId"
                    :href="media[item.index].url"
                    :data-ui-gallery-open-url="media[item.index].viewUrl || null"
                    :data-ui-gallery-subtitles-url="subtitlesUrl(media[item.index])"
                    :type="videoType(media[item.index])"
                    :title="media[item.index].fileName"
                    :aria-label="isMoreTile(item.index) ? moreLabel() : videoLabel(media[item.index])"
                >
                    <video
                        v-if="!failed[media[item.index].guid]"
                        v-watch-video="media[item.index].guid"
                        :src="active[media[item.index].guid] ? media[item.index].url + '#t=0.001' : null"
                        :preload="active[media[item.index].guid] ? 'metadata' : null"
                        muted
                        playsinline
                        aria-hidden="true"
                        tabindex="-1"
                        @loadedmetadata="measured($event.target, media[item.index])"
                        @error="failed[media[item.index].guid] = true"
                    ></video>
                    <i v-else class="ti ti-movie post-files-video-icon" aria-hidden="true"></i>
                    <template v-if="!isMoreTile(item.index) && !failed[media[item.index].guid]">
                        <span class="post-files-video-play" aria-hidden="true">
                            <i class="ti ti-player-play-filled"></i>
                        </span>
                        <span v-if="durationText(media[item.index])" class="post-files-video-duration" aria-hidden="true">{{
                            durationText(media[item.index])
                        }}</span>
                    </template>
                    <span v-if="isMoreTile(item.index)" class="post-files-image-more" aria-hidden="true">+{{ layout.hidden }}</span>
                </a>
                <a
                    v-else
                    class="post-files-tile post-files-image"
                    :style="{ width: px(item.width) }"
                    :data-ui-gallery="galleryId"
                    :href="media[item.index].url"
                    :data-ui-gallery-open-url="media[item.index].viewUrl || null"
                    :data-ui-gallery-src="media[item.index].displayUrl || null"
                    :title="media[item.index].fileName"
                    :aria-label="isMoreTile(item.index) ? moreLabel() : null"
                >
                    <img
                        loading="lazy"
                        decoding="async"
                        :src="source(media[item.index], item.width)"
                        :alt="media[item.index].fileName"
                        :width="Math.round(item.width)"
                        :height="Math.round(row.height)"
                    >
                    <span v-if="isMoreTile(item.index)" class="post-files-image-more" aria-hidden="true">+{{ layout.hidden }}</span>
                </a>
            </template>
        </div>
        <a
            v-for="file in hiddenMedia"
            :key="file.guid"
            hidden
            :data-ui-gallery="galleryId"
            :href="file.url"
            :data-ui-gallery-open-url="file.viewUrl || null"
            :data-ui-gallery-subtitles-url="subtitlesUrl(file)"
            :type="isVideo(file) ? videoType(file) : null"
            :data-ui-gallery-src="file.displayUrl || null"
            :title="file.fileName"
        >{{ file.fileName }}</a>
    </div>
</template>

<script>
/**
 * The media of `AttachedFiles.vue` in justified rows (`justify.js`), in the files' order: the images, and the videos
 * as tiles. At most two rows, the last tile says how many follow, the media after it stays in the media viewer as
 * hidden links. A component of its own, so a resize re-renders the rows only.
 *
 * A tile's ratio is the file's `width / height`, else (video) the one measured on `loadedmetadata`, else 16:9 for a
 * video and 1 for an image. A video tile never plays: its `<video>` is a muted, inert still frame (loaded once the
 * tile is near the viewport), a click anywhere on the tile opens the media viewer, which plays it. A video the
 * browser can't load shows an icon on the tile surface instead.
 *
 * The tiles and the hidden links hand the media viewer `file.viewUrl` (its "Open" button) and, for a video, the URL
 * of the file's variants (its subtitles).
 *
 * @since 1.20
 */
import { apiUrl, i18n } from '@humhub/vue';
import { justify } from '../justify.js';
import { VIDEO_TYPES, extensionOf } from '../mediaTypes.js';
import { formatDuration } from '../../../../vue/audio/formatDuration.js';

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

// The frame of a video is loaded when its tile gets this near the viewport (one observer for all tiles of the page)
const NEAR_VIEWPORT = '200px';
const VIDEO_FALLBACK_RATIO = 16 / 9;
const nearCallbacks = new Map();
let nearObserver = null;

function whenNear(el, callback) {
    if (typeof IntersectionObserver === 'undefined') {
        callback();
        return;
    }
    nearObserver ??= new IntersectionObserver(
        (entries) =>
            entries.forEach(({ target, isIntersecting }) => {
                if (isIntersecting) {
                    nearCallbacks.get(target)?.();
                    unwatch(target);
                }
            }),
        { rootMargin: NEAR_VIEWPORT },
    );
    nearCallbacks.set(el, callback);
    nearObserver.observe(el);
}

function unwatch(el) {
    nearObserver?.unobserve(el);
    nearCallbacks.delete(el);
}

const hasSize = (file) => file.width > 0 && file.height > 0;

export default {
    name: 'AttachedMedia',
    props: {
        // The images and the videos, see AttachedFiles.vue
        media: { type: Array, required: true },
        // The record's group in the media viewer
        galleryId: { type: String, required: true },
    },
    directives: {
        // A video element is watched from its mount to its unmount: a tile that moves to another row gets a new element
        watchVideo: {
            mounted(el, { value, instance }) {
                instance.watchVideo(el, value);
            },
            beforeUnmount(el, { instance }) {
                instance.unwatchVideo(el);
            },
        },
    },
    data() {
        // `box`: width and sizes of the area in px, once measured. By guid: `sizes` the ratio and duration measured on a
        // video, `active` a video tile near the viewport (loads its frame), `failed` a video the browser can't load.
        return { box: null, sizes: {}, active: {}, failed: {} };
    },
    computed: {
        layout() {
            const box = this.box || {
                width: SIZES.width * 16,
                rowHeight: SIZES.rowHeight * 16,
                singleHeight: SIZES.singleHeight * 16,
                gap: SIZES.gap * 16,
            };

            return justify(this.media.map((file) => this.ratioOf(file)), { ...box, maxRows: MAX_ROWS });
        },
        shownCount() {
            return this.layout.rows.reduce((count, row) => count + row.items.length, 0);
        },
        hiddenMedia() {
            return this.media.slice(this.shownCount);
        },
    },
    created() {
        // The guids of the images showing the display version, deliberately not reactive
        this.displayed = new Set();
        // The video elements being watched
        this.videos = new Set();
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
        this.videos.forEach((el) => unwatch(el));
        this.videos.clear();
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
        isVideo(file) {
            return !file.previewUrl;
        },
        videoType(file) {
            return VIDEO_TYPES[extensionOf(file)];
        },
        // The viewer adds the subtitles of a video to its player, see humhub.ui.gallery.js
        // A file the API knows (with an id) may have subtitles
        subtitlesUrl(file) {
            return this.isVideo(file) && file.id ? apiUrl(`file/${file.id}/variants`) : null;
        },
        ratioOf(file) {
            if (hasSize(file)) {
                return file.width / file.height;
            }

            return this.sizes[file.guid]?.ratio ?? (this.isVideo(file) ? VIDEO_FALLBACK_RATIO : 1);
        },
        // From the file, else what the tile measured
        durationText(file) {
            return formatDuration(file.duration ?? this.sizes[file.guid]?.duration);
        },
        measured(video, file) {
            const known = this.sizes[file.guid];
            const size = { ...known };

            if (video.videoWidth > 0 && video.videoHeight > 0) {
                size.ratio = video.videoWidth / video.videoHeight;
            }
            if (Number.isFinite(video.duration)) {
                size.duration = video.duration;
            }
            if (size.ratio !== known?.ratio || size.duration !== known?.duration) {
                this.sizes[file.guid] = size;
            }
        },
        // A video element mounted: loads the frame once the tile is near the viewport
        watchVideo(el, guid) {
            if (this.videos.has(el)) {
                return;
            }
            this.videos.add(el);
            whenNear(el, () => {
                this.active[guid] = true;
            });
        },
        unwatchVideo(el) {
            this.videos.delete(el);
            unwatch(el);
        },
        videoLabel(file) {
            const duration = this.durationText(file);
            const fileName = file.fileName;

            return duration
                ? i18n.t('FileModule.base', '{fileName}, video, {duration}', { fileName, duration })
                : i18n.t('FileModule.base', '{fileName}, video', { fileName });
        },
        isMoreTile(index) {
            return this.layout.hidden > 0 && index === this.shownCount - 1;
        },
        moreLabel() {
            const fileName = this.media[this.shownCount - 1].fileName;
            const count = this.layout.hidden;

            return this.hiddenMedia.every((file) => !this.isVideo(file))
                ? i18n.t('FileModule.base', '{fileName}, {count,plural,=1{one more image} other{# more images}}', {
                    fileName,
                    count,
                })
                : i18n.t(
                    'FileModule.base',
                    '{fileName}, {count,plural,=1{one more image or video} other{# more images and videos}}',
                    { fileName, count },
                );
        },
        // The preview fits into PREVIEW_SIZE², a larger tile (or a sharper screen) gets the display version and keeps
        // it while the area shrinks. An original that fits into the preview is the preview.
        source(file, width) {
            if (this.displayed.has(file.guid)) {
                return file.displayUrl;
            }

            const fits = hasSize(file) && file.width <= PREVIEW_SIZE && file.height <= PREVIEW_SIZE;
            if (!file.displayUrl || fits) {
                return file.previewUrl;
            }

            const previewWidth = Math.min(PREVIEW_SIZE, PREVIEW_SIZE * this.ratioOf(file));
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
