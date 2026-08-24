<template>
    <div class="hideOnEdit">
        <div v-if="images.length || videos.length || audios.length" class="post-files">
            <div v-if="audios.length" class="post-files-audio d-flex flex-wrap justify-content-center">
                <div class="col-media col-12">
                    <audio
                        v-for="file in audios"
                        :key="file.guid"
                        :src="file.url"
                        controls
                        preload="metadata"
                        class="w-100"
                    ></audio>
                </div>
            </div>

            <div v-if="videos.length" class="post-files-videos d-flex flex-wrap justify-content-center">
                <div v-for="file in videos" :key="file.guid" :class="columnClass(videos.length, true)">
                    <a
                        :data-ui-gallery="galleryId"
                        :href="file.url + '#.' + extension(file)"
                        :title="file.fileName"
                        class="d-flex align-items-center justify-content-center h-100 w-100"
                    >
                        <video :src="file.url + '#t=0.001'" controls preload="metadata" height="130"></video>
                    </a>
                </div>
            </div>

            <div v-if="images.length" class="post-files-images d-flex flex-wrap justify-content-center">
                <div v-for="file in images" :key="file.guid" :class="columnClass(images.length)">
                    <a :data-ui-gallery="galleryId" :href="file.url + '#.jpeg'" :title="file.fileName">
                        <img class="animated fadeIn" :src="file.previewUrl" :alt="file.fileName">
                    </a>
                </div>
            </div>
        </div>

        <div v-if="others.length" class="post-files-list">
            <a
                v-for="file in others"
                :key="file.guid"
                :href="file.url"
                target="_blank"
                rel="noopener"
                class="d-block"
            ><i class="fa fa-file-o" aria-hidden="true"></i> {{ file.fileName }} <small class="text-body-secondary">({{ humanSize(file.size) }})</small></a>
        </div>
    </div>
</template>

<script>
/**
 * Renders a comment's attachments from the structured `files` shape
 * (`{id, guid, mimeType, size, fileName, mimeIcon, url, previewUrl}` — see
 * `humhub\modules\file\serializers\FileSerializer::file()`), replacing the
 * server-rendered `ShowFiles` HTML the legacy island payload used to carry.
 *
 * Markup mirrors `file/widgets/views/showFiles.php` where it matters for theme
 * CSS (`.hideOnEdit`, `.post-files`, `.post-files-images/-videos/-audio`,
 * `.col-media` grid buckets) and reuses the same `data-ui-gallery` attribute,
 * so the existing core gallery/lightbox addition picks the media up unchanged
 * (the comment entry root runs `v-additions`).
 *
 * Deliberate simplifications vs. `ShowFiles` (documented deviations):
 *  - Audio renders as native `<audio controls>` instead of the legacy
 *    `JPlayerPlaylistWidget` playlist.
 *  - The trailing file list is a plain link list instead of the `file.Preview`
 *    JsWidget (whose popover/preview machinery is stream-entry oriented).
 *  - The grid column buckets use the non-fluid breakpoints only — the fluid
 *    theme layout check is server-side (`ThemeHelper::isFluid()`) and not
 *    worth a config roundtrip for comment-sized media grids.
 *
 * @since 1.20
 */
const VIDEO_EXTENSIONS = { webm: 'video/webm', mp4: 'video/mp4', ogv: 'video/ogg', mov: 'video/quicktime' };

export default {
    props: {
        // File shapes, see the class docblock.
        files: { type: Array, required: true },
        // Scopes the lightbox gallery per comment (mirrors ShowFiles'
        // per-object `gallery-<uniqueId>` grouping).
        contextId: { type: [Number, String], required: true },
    },
    computed: {
        galleryId() {
            return 'gallery-comment-' + this.contextId;
        },
        images() {
            return this.files.filter((file) => !!file.previewUrl);
        },
        videos() {
            return this.files.filter((file) => !file.previewUrl && VIDEO_EXTENSIONS[this.extension(file)]);
        },
        audios() {
            return this.files.filter((file) => !file.previewUrl && this.extension(file) === 'mp3');
        },
        others() {
            return this.files.filter(
                (file) => !file.previewUrl && !VIDEO_EXTENSIONS[this.extension(file)] && this.extension(file) !== 'mp3',
            );
        },
    },
    methods: {
        extension(file) {
            const name = String(file.fileName || '');
            const dot = name.lastIndexOf('.');
            return dot === -1 ? '' : name.slice(dot + 1).toLowerCase();
        },
        // Mirrors showFiles.php's $getColumnClass() for the non-fluid layout.
        columnClass(count, enlarge = false) {
            let bsColumns = 6;
            let bsColumnsMd = 6;
            let bsColumnsLg = enlarge ? 6 : 4;
            if (count === 1) {
                bsColumns = 12;
                bsColumnsMd = 12;
                bsColumnsLg = enlarge ? 12 : 6;
            }
            if (count === 2 && !enlarge) {
                bsColumnsMd = 6;
                bsColumnsLg = 6;
            }
            return `col-media col-${bsColumns} col-lg-${bsColumnsMd} col-xl-${bsColumnsLg}`;
        },
        humanSize(size) {
            const bytes = Number(size) || 0;
            if (bytes >= 1048576) {
                return (bytes / 1048576).toFixed(1) + ' MB';
            }
            if (bytes >= 1024) {
                return (bytes / 1024).toFixed(1) + ' KB';
            }
            return bytes + ' B';
        },
    },
};
</script>
