<template>
    <div class="file-versions">
        <div v-if="loading" class="text-center p-2">
            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
            <span class="visually-hidden" role="status">{{ loadingLabel }}</span>
        </div>

        <p v-else-if="failed" class="text-danger m-0">{{ errorLabel }}</p>

        <ol v-else class="list-group list-group-flush file-versions-list">
            <li v-for="entry in versions" :key="entry.version" class="list-group-item file-version">
                <UserImage v-if="entry.createdBy" v-bind="entry.createdBy" :size="32" :link="false" />
                <div class="file-version-body">
                    <div class="file-version-title">
                        {{ titleOf(entry) }}
                        <span v-if="entry.current" class="badge">{{ currentLabel }}</span>
                    </div>
                    <div class="file-version-meta">
                        <time :datetime="entry.createdAt">{{ formatDate(entry.createdAt) }}</time>
                        · {{ shortSize(entry.size) }}
                    </div>
                </div>
                <a
                    class="btn btn-ghost btn-icon-only"
                    :href="entry.downloadUrl"
                    :aria-label="downloadLabelOf(entry)"
                    :title="downloadLabelOf(entry)"
                ><i class="ti ti-download" aria-hidden="true"></i></a>
            </li>
        </ol>
    </div>
</template>

<script>
/**
 * The versions of a file, newest first: who saved each one, when, how big it was, and a
 * download of it. A version is kept whenever a file's content is replaced on a record that
 * enables the file history (`ActiveRecord::$fileManagerEnableHistory`, e.g. a cfiles file
 * edited in an office suite); without one, the list is the current version alone.
 *
 * Loads on mount: it is meant for a page that opens a file client-side, such as a file's
 * details pane.
 *
 * Endpoint: `GET api/v2/file/<id>/versions` (see `file\controllers\api\FileController`).
 *
 * @since 1.20
 */
import { apiUrl, client, getConfig, i18n, log } from '@humhub/vue';

const SIZE_UNITS = ['B', 'KB', 'MB', 'GB', 'TB'];

export default {
    i18nCategories: ['FileModule.base', 'base'],
    props: {
        /** The id of the platform file (not of the record it is attached to). */
        fileId: { type: Number, required: true },
    },
    data() {
        return {
            versions: [],
            loading: true,
            failed: false,
        };
    },
    computed: {
        currentLabel: () => i18n.t('FileModule.base', 'Current'),
        loadingLabel: () => i18n.t('base', 'Loading...'),
        errorLabel: () => i18n.t('FileModule.base', 'The versions could not be loaded.'),
    },
    mounted() {
        client.get(apiUrl('file/' + this.fileId + '/versions'))
            .then((response) => {
                this.versions = Array.isArray(response.results) ? response.results : [];
                this.loading = false;
            })
            .catch((e) => {
                this.failed = true;
                this.loading = false;
                log.error(e);
            });
    },
    methods: {
        /** "Jane Doe uploaded v3", or "v3" where nobody is known. */
        titleOf(entry) {
            const version = 'v' + entry.version;
            return entry.createdBy
                ? i18n.t('FileModule.base', '{name} uploaded {version}', { name: entry.createdBy.displayName, version })
                : version;
        },
        downloadLabelOf(entry) {
            return i18n.t('FileModule.base', 'Download version {version}', { version: entry.version });
        },
        formatDate(stamp) {
            if (!stamp) {
                return '';
            }
            const locale = getConfig('i18n').language || undefined;
            return new Intl.DateTimeFormat(locale, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(stamp));
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
