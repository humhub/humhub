<template>
    <div v-if="media.length" class="post-files">
        <AttachedMedia :media="media" :gallery-id="galleryId" />
    </div>

    <div v-if="listed.length" v-additions class="list-group file-list">
        <div :id="collapsible ? listId : null" class="file-list-rows" role="list">
            <FileRow
                v-for="file in listedHead"
                :key="file.guid"
                :file="file"
                :playable="isPlayable(file)"
                :playlist="playlist"
            />
            <div
                v-if="collapsible"
                ref="more"
                class="file-list-more"
                :class="{ show: expanded }"
                :inert="!expanded"
                role="none"
            >
                <div class="file-list-more-inner" role="none">
                    <FileRow
                        v-for="file in listedTail"
                        :key="file.guid"
                        :file="file"
                        :playable="isPlayable(file)"
                        :playlist="playlist"
                        @current="expanded = true"
                    />
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
 * Renders the files attached to a record: the media grid (images and video tiles in `components/AttachedMedia.vue`)
 * followed by the list of the files. Mounted as island by `file\widgets\ShowFiles`,
 * and nested in other islands such as the comment section (`CommentEntry.vue`).
 *
 * Files are the API's file shape (`humhub\modules\file\serializers\FileSerializer::file()`), including `width`,
 * `height` and `duration` of the media for the layout. A server-side caller can add two presentation hints (`ShowFiles`; the
 * comment payloads carry `viewUrl` per caller):
 *
 *  - `viewUrl` - open the file in the global modal under this URL instead of downloading it.
 *  - `highlight` - mark the file as a search hit.
 *
 * The list rows are the core `FileRow`, the file row of docs/ui/file-upload.md, resolved through the global component
 * registry (`FileVueAsset` depends on the core bundle). Audio files are rows of the list too, with a play button
 * (`FileRow` `playable`) while the media is shown (`preview`): one plays after the other, and "Show all" opens when the
 * next one is hidden. A long list shows two rows and "Show all". The list runs
 * `v-additions` for the thumbnail popovers of its rows; the media viewer and the tooltips bind by delegation.
 *
 * @since 1.20
 */
import { i18n } from '@humhub/vue';
import AttachedMedia from './components/AttachedMedia.vue';
import { AUDIO_EXTENSIONS, VIDEO_EXTENSIONS, extensionOf } from './mediaTypes.js';

// A long list shows LIST_HEAD rows and the rest behind "Show all", from COLLAPSE_FROM files on (never hides one row)
const LIST_HEAD = 2;
const COLLAPSE_FROM = 4;

let uid = 0;

export default {
    components: { AttachedMedia },
    i18nCategories: ['FileModule.base'],
    props: {
        // File shapes, see the class docblock.
        files: { type: Array, required: true },
        // Scopes the media viewer's gallery to this record's media.
        galleryId: { type: String, required: true },
        // Render the media grid and the audio players at all (`ShowFiles::$preview`).
        preview: { type: Boolean, default: true },
        // Drop the images and videos from the list below - they are already tiles of the media grid. Audio files stay:
        // the list is where they play
        // (the `excludeMediaFilesPreview` setting of the file module).
        excludeMedia: { type: Boolean, default: false },
    },
    data() {
        return { expanded: false };
    },
    computed: {
        // The tiles of the media grid in the files' order: the images, and the videos the browser can play
        media() {
            return this.preview ? this.files.filter((file) => this.isImage(file) || this.isVideo(file)) : [];
        },
        // Without `excludeMedia` all files, otherwise those the grid doesn't render - a media file it has no tile
        // for (an AVI video) stays in the list, so does every audio file.
        listed() {
            if (!this.excludeMedia) {
                return this.files;
            }

            const rendered = new Set(this.media);

            return this.files.filter((file) => !rendered.has(file));
        },
        // The audio files of the list in its order: the next one plays when one ends
        playlist() {
            return this.preview ? this.listed.filter((file) => this.isAudio(file)) : [];
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
        isImage(file) {
            return !!file.previewUrl;
        },
        // A video by its extension, but only without an image preview - an image preview always wins
        isVideo(file) {
            return !file.previewUrl && VIDEO_EXTENSIONS.includes(extensionOf(file));
        },
        isAudio(file) {
            return !file.previewUrl && AUDIO_EXTENSIONS.includes(extensionOf(file));
        },
        isPlayable(file) {
            return this.preview && this.isAudio(file);
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
