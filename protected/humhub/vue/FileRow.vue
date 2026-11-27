<template>
    <div
        class="list-group-item file-row"
        :class="{ 'is-uploading': uploading, 'is-processing': processing, 'is-invalid': invalid }"
        role="listitem"
        :data-preview-guid="file.guid"
    >
        <i class="ti file-row-icon" :class="'ti-' + (file.icon || 'file')" aria-hidden="true"></i>
        <span class="file-row-text" v-bind="popoverAttributes">
            <span
                v-if="!linked"
                class="file-row-name"
            ><span class="file-row-name-head">{{ name.head }}</span><span class="file-row-name-tail">{{ name.tail }}</span></span>
            <a
                v-else-if="file.viewUrl"
                class="file-row-name"
                :class="{ highlight: !!file.highlight }"
                :href="file.viewUrl"
                data-bs-target="#globalModal"
            ><span class="file-row-name-head">{{ name.head }}</span><span class="file-row-name-tail">{{ name.tail }}</span></a>
            <a
                v-else
                class="file-row-name"
                :class="{ highlight: !!file.highlight }"
                :href="file.url"
                target="_blank"
                rel="noopener"
                v-bind="downloadAttributes"
            ><span class="file-row-name-head">{{ name.head }}</span><span class="file-row-name-tail">{{ name.tail }}</span></a>
            <span class="file-row-meta">({{ size }})</span>
            <span v-if="invalid" class="invalid-feedback d-block">{{ error }}</span>
        </span>
        <ProgressFrame
            v-if="uploading"
            class="file-row-progress"
            :value="progress"
            :indeterminate="processing"
            :label="uploadingLabel"
        />
        <button
            v-if="action === 'remove'"
            type="button"
            class="btn btn-ghost btn-ghost-danger btn-sm btn-icon-only file-row-action"
            :aria-label="removeLabel"
            :aria-disabled="busy ? 'true' : null"
            :aria-busy="busy ? 'true' : null"
            :data-bs-title="removeLabel"
            data-bs-toggle="tooltip"
            @click="onRemove"
        ><span v-if="busy" class="spinner-border spinner-border-sm" aria-hidden="true"></span><i v-else class="ti ti-trash" aria-hidden="true"></i></button>
        <a
            v-else-if="linked"
            class="btn btn-ghost btn-sm btn-icon-only file-row-action"
            :href="file.downloadUrl || file.url"
            v-bind="downloadAttributes"
            :aria-label="downloadLabel"
            :data-bs-title="downloadLabel"
            data-bs-toggle="tooltip"
        ><i class="ti ti-download" aria-hidden="true"></i></a>
    </div>
</template>

<script>
/**
 * A file row of docs/ui/file-upload.md: icon, the name shortened in the middle, the size, and download or remove as
 * the trailing action. Lists nest it by tag (`<FileRow>`), e.g. `AttachedFiles` of the file module.
 *
 * - `file`: the API's file shape (`file\serializers\FileSerializer::file()`), see `AttachedFiles.vue`. A file picked
 *   in a form has only `fileName` and `size` until the server answers: the generic icon, the name as text.
 * - `action`: `download` (default), or `remove`, which emits `remove`.
 * - `progress`: 0–100 while the file uploads: a `ProgressFrame` runs around the row, the name is no link yet. At 100
 *   the file is sent and the server processes it until the row is done (`.is-processing`): the frame runs as a
 *   segment, without a value.
 * - `error`: the reason a file was refused, shown in the row (`.is-invalid`); it ends the progress.
 * - `busy`: the removal is under way (the file is being deleted): remove shows a spinner and does nothing meanwhile.
 *   `aria-disabled`, not `disabled`, so the button keeps the focus.
 * - `popoverPlacement`: where the image preview opens on hover (`right`, `top`, …); empty for none.
 *
 * The labels come from `FileModule.base`, remove's from `base`: an island nesting the row preloads these categories.
 *
 * @since 1.20
 */
import { i18n } from '@humhub/vue';
import ProgressFrame from './ProgressFrame.vue';
import { formatFileSize } from './upload/fileSize.js';

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

export default {
    name: 'FileRow',
    components: { ProgressFrame },
    props: {
        file: { type: Object, required: true },
        action: { type: String, default: 'download', validator: (value) => ['download', 'remove'].includes(value) },
        progress: { type: Number, default: null },
        error: { type: String, default: null },
        busy: { type: Boolean, default: false },
        popoverPlacement: { type: String, default: 'right' },
    },
    emits: ['remove'],
    computed: {
        invalid() {
            return Boolean(this.error);
        },
        uploading() {
            return this.progress !== null && !this.invalid;
        },
        // Sent, the server's answer is outstanding
        processing() {
            return this.uploading && this.progress >= 100;
        },
        // A file being uploaded, refused or not uploaded yet has nothing to link to
        linked() {
            return !this.uploading && !this.invalid && Boolean(this.file.viewUrl || this.file.url);
        },
        // Head and tail, so the head can be cut off and the extension stays
        name() {
            const fileName = String(this.file.fileName || '');
            const dot = fileName.lastIndexOf('.');

            return dot > 0 ? { head: fileName.slice(0, dot), tail: fileName.slice(dot) } : { head: fileName, tail: '' };
        },
        size() {
            return formatFileSize(this.file.size);
        },
        downloadLabel() {
            return i18n.t('FileModule.base', 'Download {fileName}', { fileName: this.file.fileName });
        },
        removeLabel() {
            return i18n.t('base', 'Remove {label}', { label: this.file.fileName });
        },
        uploadingLabel() {
            return this.processing
                ? i18n.t('FileModule.base', 'Processing {fileName}', { fileName: this.file.fileName })
                : i18n.t('FileModule.base', 'Uploading {fileName}', { fileName: this.file.fileName });
        },
        // What the mobile app reads off a download link (FileDownload::getFileDataAttributes())
        downloadAttributes() {
            return {
                'data-pjax-prevent': '',
                'data-file-download': '',
                'data-file-url': this.file.downloadUrl || this.file.url,
                'data-file-name': this.file.fileName,
                'data-file-mime': this.file.mimeType,
            };
        },
        // The thumbnail popover, initialized by the core `popover` addition (selector `.po`)
        popoverAttributes() {
            if (!this.file.previewUrl || !this.popoverPlacement) {
                return {};
            }

            return {
                class: 'po',
                'data-bs-toggle': 'popover',
                'data-bs-trigger': 'hover',
                'data-bs-placement': this.popoverPlacement,
                'data-bs-container': 'body',
                'data-bs-delay': '100',
                'data-bs-html': 'true',
                'data-bs-content': `<img alt="${escapeHtml(this.file.fileName)}" src="${escapeHtml(this.file.previewUrl)}" />`,
            };
        },
    },
    methods: {
        onRemove() {
            if (!this.busy) {
                this.$emit('remove');
            }
        },
    },
};
</script>
