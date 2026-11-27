<template>
    <div class="list-group-item file-row" role="listitem" :data-preview-guid="file.guid">
        <i class="ti file-row-icon" :class="'ti-' + (file.icon || 'file')" aria-hidden="true"></i>
        <span class="file-row-text" v-bind="popoverAttributes">
            <a
                v-if="file.viewUrl"
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
        </span>
        <a
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
 * One row of the file list of `AttachedFiles.vue`, the file row of docs/ui/file-upload.md: icon, the name shortened
 * in the middle, the size, and a download button.
 *
 * @since 1.20
 */
import { i18n } from '@humhub/vue';

const SIZE_UNITS = ['B', 'KB', 'MB', 'GB', 'TB'];

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

export default {
    name: 'FileRow',
    props: {
        // The file shape, see AttachedFiles.vue
        file: { type: Object, required: true },
    },
    computed: {
        // Head and tail, so the head can be cut off and the extension stays
        name() {
            const fileName = String(this.file.fileName || '');
            const dot = fileName.lastIndexOf('.');

            return dot > 0 ? { head: fileName.slice(0, dot), tail: fileName.slice(dot) } : { head: fileName, tail: '' };
        },
        size() {
            let value = Number(this.file.size) || 0;
            let unit = 0;

            while (value >= 1024 && unit < SIZE_UNITS.length - 1) {
                value /= 1024;
                unit++;
            }

            const formatted = value.toLocaleString(undefined, { maximumFractionDigits: unit === 0 ? 0 : 1 });

            return `${formatted} ${SIZE_UNITS[unit]}`;
        },
        downloadLabel() {
            return i18n.t('FileModule.base', 'Download {fileName}', { fileName: this.file.fileName });
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
            if (!this.file.previewUrl) {
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
                'data-bs-content': `<img alt="${escapeHtml(this.file.fileName)}" src="${escapeHtml(this.file.previewUrl)}" />`,
            };
        },
    },
};
</script>
