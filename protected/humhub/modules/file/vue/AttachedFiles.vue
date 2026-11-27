<template>
    <div v-if="hasMedia" class="post-files">
        <div v-if="audios.length" class="post-files-audio">
            <div v-for="file in audios" :key="file.guid" class="post-files-audio-item">
                <div class="text-truncate small">{{ file.fileName }}</div>
                <audio :src="file.url" controls preload="metadata" :aria-label="file.fileName"></audio>
            </div>
        </div>

        <div v-if="videos.length" class="post-files-videos">
            <a
                v-for="file in videos"
                :key="file.guid"
                class="post-files-video"
                :data-ui-gallery="galleryId"
                :href="file.url"
                :type="videoType(file)"
                :title="file.fileName"
            >
                <video :src="file.url + '#t=0.001'" controls preload="metadata"></video>
            </a>
        </div>

        <AttachedImages v-if="images.length" :images="images" :gallery-id="galleryId" />
    </div>

    <div v-if="listed.length" v-additions class="list-group file-list">
        <div :id="collapsible ? listId : null" class="file-list-rows" role="list">
            <FileRow v-for="file in listedHead" :key="file.guid" :file="file" />
            <div
                v-if="collapsible"
                ref="more"
                class="file-list-more"
                :class="{ show: expanded }"
                :inert="!expanded"
                role="none"
            >
                <div class="file-list-more-inner" role="none">
                    <FileRow v-for="file in listedTail" :key="file.guid" :file="file" />
                </div>
            </div>
        </div>
        <div v-if="collapsible" class="list-group-item file-row file-list-toggle">
            <i class="ti ti-files file-row-icon" aria-hidden="true"></i>
            <span :id="countId" class="file-row-text">{{ countLabel }}</span>
            <button
                ref="toggle"
                type="button"
                class="btn btn-ghost btn-sm file-row-action"
                :aria-expanded="expanded ? 'true' : 'false'"
                :aria-controls="listId"
                :aria-describedby="countId"
                @click="toggle"
            >
                {{ expanded ? showLessLabel : showAllLabel }}
                <i class="ti" :class="expanded ? 'ti-chevron-up' : 'ti-chevron-down'" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</template>

<script>
/**
 * Renders the files attached to a record: the media area (audio and video players, the images in
 * `components/AttachedImages.vue`) followed by the list of the files. Mounted as island by `file\widgets\ShowFiles`,
 * and nested in other islands such as the comment section (`CommentEntry.vue`).
 *
 * Files are the API's file shape (`humhub\modules\file\serializers\FileSerializer::file()`), including `width` and
 * `height` of an image for the layout. A server-side caller can add two presentation hints the API doesn't carry
 * (see `ShowFiles`):
 *
 *  - `viewUrl` - open the file in the global modal under this URL instead of downloading it.
 *  - `highlight` - mark the file as a search hit.
 *
 * The list rows are `components/FileRow.vue`, the file row of docs/ui/file-upload.md; a long list shows three rows
 * and "Show all". The list runs `v-additions` for the thumbnail popovers of its rows; the media viewer and the
 * tooltips bind by delegation.
 *
 * @since 1.20
 */
import { i18n } from '@humhub/vue';
import AttachedImages from './components/AttachedImages.vue';
import FileRow from './components/FileRow.vue';

// The media viewer needs the type, otherwise it shows a video as an image
const VIDEO_TYPES = {
    webm: 'video/webm',
    mp4: 'video/mp4',
    ogv: 'video/ogg',
    mov: 'video/quicktime',
};
const VIDEO_EXTENSIONS = Object.keys(VIDEO_TYPES);
// Formats every current browser plays in `<audio>` - Ogg is left out, Safari's support is unreliable
const AUDIO_EXTENSIONS = ['mp3', 'wav', 'm4a', 'aac', 'flac'];

// A long list shows LIST_HEAD rows and the rest behind "Show all", from COLLAPSE_FROM files on (never hides one row)
const LIST_HEAD = 2;
const COLLAPSE_FROM = 4;

let uid = 0;

export default {
    components: { AttachedImages, FileRow },
    i18nCategories: ['FileModule.base'],
    props: {
        // File shapes, see the class docblock.
        files: { type: Array, required: true },
        // Scopes the media viewer's gallery to this record's media.
        galleryId: { type: String, required: true },
        // Render the media area at all (`ShowFiles::$preview`).
        preview: { type: Boolean, default: true },
        // Drop media files from the list below - they are already in the media area
        // (the `excludeMediaFilesPreview` setting of the file module).
        excludeMedia: { type: Boolean, default: false },
    },
    data() {
        return { expanded: false };
    },
    computed: {
        images() {
            return this.preview ? this.files.filter((file) => !!file.previewUrl) : [];
        },
        videos() {
            return this.preview ? this.mediaBucket(VIDEO_EXTENSIONS) : [];
        },
        audios() {
            return this.preview ? this.mediaBucket(AUDIO_EXTENSIONS) : [];
        },
        hasMedia() {
            return this.images.length > 0 || this.videos.length > 0 || this.audios.length > 0;
        },
        // Without `excludeMedia` all files, otherwise those the media area doesn't render -
        // a media file it has no preview for (an AVI video, an Ogg audio) stays in the list.
        listed() {
            if (!this.excludeMedia) {
                return this.files;
            }

            const rendered = new Set([...this.images, ...this.videos, ...this.audios]);

            return this.files.filter((file) => !rendered.has(file));
        },
        collapsible() {
            return this.listed.length >= COLLAPSE_FROM;
        },
        listedHead() {
            return this.collapsible ? this.listed.slice(0, LIST_HEAD) : this.listed;
        },
        listedTail() {
            return this.collapsible ? this.listed.slice(LIST_HEAD) : [];
        },
        // The list is what "Show all" controls: the wrapper around the hidden rows has no role, a reference to it
        // would be dropped
        listId() {
            return `attached-files-${this.uid}-list`;
        },
        countId() {
            return `attached-files-${this.uid}-count`;
        },
        countLabel() {
            const total = this.listed.length;
            if (this.expanded) {
                return i18n.t('FileModule.base', '{total,plural,one{# file} other{# files}}', { total });
            }

            return i18n.t('FileModule.base', '{shown} of {total,plural,one{# file} other{# files}}', {
                shown: LIST_HEAD,
                total,
            });
        },
        showAllLabel() {
            return i18n.t('FileModule.base', 'Show all');
        },
        showLessLabel() {
            return i18n.t('FileModule.base', 'Show less');
        },
    },
    created() {
        this.uid = ++uid;
        // A search hit among the hidden rows shows them all
        this.expanded = this.listedTail.some((file) => file.highlight);
    },
    methods: {
        extension(file) {
            const name = String(file.fileName || '');
            const dot = name.lastIndexOf('.');

            return dot === -1 ? '' : name.slice(dot + 1).toLowerCase();
        },
        videoType(file) {
            return VIDEO_TYPES[this.extension(file)];
        },
        // A file belongs to a media bucket by its extension, but only when it has no
        // image preview - an image preview always wins.
        mediaBucket(extensions) {
            return this.files.filter((file) => !file.previewUrl && extensions.includes(this.extension(file)));
        },
        // "Show all" hands the focus on to the first revealed row (docs/ui/keyboard.md, "Moving the focus"),
        // "Show less" keeps it on the button
        toggle() {
            const focused = document.activeElement === this.$refs.toggle;
            this.expanded = !this.expanded;
            if (this.expanded && focused) {
                this.$nextTick(() => this.$refs.more.querySelector('.file-row-name')?.focus());
            }
        },
    },
};
</script>
