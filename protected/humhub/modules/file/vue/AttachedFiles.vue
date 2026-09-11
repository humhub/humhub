<template>
    <div v-if="hasMedia" v-additions class="post-files">
        <div v-if="audios.length" class="post-files-audio d-flex flex-wrap justify-content-center">
            <div v-for="file in audios" :key="file.guid" class="col-media col-12">
                <div class="text-truncate small">{{ file.fileName }}</div>
                <audio :src="file.url" controls preload="metadata" class="w-100"></audio>
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

    <div v-if="listed.length" v-additions class="well post-file-list">
        <ul class="files">
            <li
                v-for="file in listed"
                :key="file.guid"
                class="file-preview-item mime"
                :class="file.mimeIcon"
                :data-preview-guid="file.guid"
            >
                <span class="file-preview-content" v-bind="popoverAttributes(file)">
                    <span :class="{ highlight: !!file.highlight }">
                        <a
                            v-if="file.viewUrl"
                            :href="file.viewUrl"
                            data-bs-target="#globalModal"
                        >{{ file.fileName }}</a>
                        <a
                            v-else
                            :href="file.url"
                            target="_blank"
                            rel="noopener"
                            data-pjax-prevent
                            data-file-download
                            :data-file-url="file.downloadUrl || file.url"
                            :data-file-name="file.fileName"
                            :data-file-mime="file.mimeType"
                        >{{ file.fileName }}</a>
                    </span>
                    <span class="time file-fileInfo"> - {{ shortSize(file.size) }}</span>
                </span>
            </li>
        </ul>
    </div>
</template>

<script>
/**
 * Renders the files attached to a record: the media grid (audio / video / image
 * previews) followed by the list of all attachments.
 *
 * This is the Vue analog of `file\widgets\ShowFiles` - which is now nothing but the
 * server-side mount point for it - and at the same time the attachment renderer of any
 * other island holding serialized files, the comment section being the first
 * (`CommentEntry.vue`). One component for both is the point: the two used to be
 * separate implementations of the same visual and had already drifted apart.
 *
 * Files are the API's file shape (`humhub\modules\file\serializers\FileSerializer::file()`
 * - `{id, guid, mimeType, size, fileName, mimeIcon, url, downloadUrl, previewUrl}`),
 * optionally refined by two **presentation hints** a server-side caller can add per file
 * (the HTTP API carries neither - see the class docblock of `ShowFiles`):
 *
 *  - `viewUrl` - open the file in the global modal under this URL instead of downloading
 *    it, i.e. the file has a viewer beyond plain download (a contributed file handler).
 *  - `highlight` - mark the entry as a search hit.
 *
 * Markup mirrors the server-rendered predecessors where it matters for theme CSS: the
 * media grid keeps `showFiles.php`'s `.post-files` / `.post-files-audio|-videos|-images`
 * structure and `.col-media` grid buckets, and the file list keeps the `.well
 * .post-file-list > ul.files > li.file-preview-item.mime.<mimeIcon>` structure the
 * `file.Preview` JsWidget rendered from its string templates. That widget stays in place
 * for the upload/edit paths, which are a different concern - there it doubles as the
 * live preview of an in-progress upload.
 *
 * Both roots run `v-additions`, so the core gallery/lightbox addition picks the media
 * links up through their `data-ui-gallery` attribute and the popover addition
 * initializes the file list's thumbnail popovers - whether this component is an island
 * of its own or nested in one whose root already applies additions (re-applying is
 * idempotent, see the mounter's own note in `humhub.vue.js`). The `viewUrl` link needs
 * nothing: `humhub.ui.modal.js` handles `a[data-bs-target="#globalModal"]` with a
 * delegated document listener.
 *
 * Deliberate deviations from the markup it replaces, all documented in
 * `docs/develop/module-migrate.md`:
 *  - Audio renders as native, individually labelled `<audio controls>` players instead
 *    of the jPlayer playlist widget `showFiles.php` used (jPlayer is gone from the
 *    platform with it).
 *  - `excludeMedia` really removes media entries from the list. The legacy widget only
 *    added a `hiddenFile` class for which no rule has existed since the Bootstrap 5
 *    migration, so the setting silently did nothing.
 *  - File sizes are formatted client-side (locale-aware number, plain unit symbol)
 *    rather than through `Yii::$app->formatter->asShortSize()`.
 *  - The media grid no longer carries a `post-files-<uniqueId>` id (nothing referenced it).
 *
 * @since 1.20
 */
const VIDEO_EXTENSIONS = ['webm', 'mp4', 'ogv', 'mov'];
const AUDIO_EXTENSIONS = ['mp3'];

// Mime icon classes of files the media grid renders a preview for - `excludeMedia`
// drops exactly these from the trailing list (`MimeHelper::getMimeIconClassByExtension()`).
const MEDIA_MIME_ICONS = ['mime-image', 'mime-video', 'mime-audio'];

const SIZE_UNITS = ['B', 'KB', 'MB', 'GB', 'TB'];

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

export default {
    props: {
        // File shapes, see the class docblock.
        files: { type: Array, required: true },
        // Scopes the lightbox gallery to this record's media.
        galleryId: { type: String, required: true },
        // Render the media grid at all (`ShowFiles::$preview`).
        preview: { type: Boolean, default: true },
        // Drop media files from the trailing list - they are already in the grid above
        // (the `excludeMediaFilesPreview` setting of the file module).
        excludeMedia: { type: Boolean, default: false },
        // Whether the active theme uses the fluid layout (`ThemeHelper::isFluid()`),
        // which widens the grid's large-breakpoint buckets.
        fluid: { type: Boolean, default: false },
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
        listed() {
            return this.excludeMedia
                ? this.files.filter((file) => !MEDIA_MIME_ICONS.includes(file.mimeIcon))
                : this.files;
        },
    },
    methods: {
        extension(file) {
            const name = String(file.fileName || '');
            const dot = name.lastIndexOf('.');

            return dot === -1 ? '' : name.slice(dot + 1).toLowerCase();
        },
        // A file belongs to a media bucket by its extension, but only when it has no
        // image preview - an image preview always wins (mirrors showFiles.php, whose
        // if/elseif chain tests the preview converter first).
        mediaBucket(extensions) {
            return this.files.filter((file) => !file.previewUrl && extensions.includes(this.extension(file)));
        },
        // Mirrors showFiles.php's $getColumnClass(). Heights matching these buckets are
        // defined in _file.scss.
        columnClass(count, enlarge = false) {
            let bsColumns = 6;
            let bsColumnsMd = this.fluid ? 4 : 6;
            let bsColumnsLg = this.fluid ? 3 : 4;

            if (count === 1) {
                bsColumns = 12;
                bsColumnsMd = this.fluid ? 6 : 12;
                bsColumnsLg = this.fluid ? 4 : 6;
            }
            if (count === 2) {
                bsColumnsMd = 6;
                bsColumnsLg = this.fluid ? 4 : 6;
            }
            if (enlarge) {
                bsColumnsLg = this.fluid ? 4 : 6;
                if (count === 1) {
                    bsColumnsLg = 12;
                }
            }

            return `col-media col-${bsColumns} col-lg-${bsColumnsMd} col-xl-${bsColumnsLg}`;
        },
        // The thumbnail popover the `file.Preview` JsWidget attached by hand, expressed
        // as data attributes instead: the core `popover` UI addition (selector `.po`)
        // initializes them, so markup Vue renders later gets them just the same.
        popoverAttributes(file) {
            if (!file.previewUrl) {
                return {};
            }

            return {
                class: 'po',
                'data-bs-toggle': 'popover',
                'data-bs-trigger': 'hover',
                'data-bs-placement': 'right',
                'data-bs-container': 'body',
                'data-bs-delay': '100',
                'data-bs-html': 'true',
                'data-bs-content': `<img alt="${escapeHtml(file.fileName)}" src="${escapeHtml(file.previewUrl)}" />`,
            };
        },
        shortSize(size) {
            let value = Number(size) || 0;
            let unit = 0;

            while (value >= 1024 && unit < SIZE_UNITS.length - 1) {
                value /= 1024;
                unit++;
            }

            const formatted = value.toLocaleString(undefined, { maximumFractionDigits: unit === 0 ? 0 : 1 });

            return `${formatted} ${SIZE_UNITS[unit]}`;
        },
    },
};
</script>
