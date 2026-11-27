/**
 * File sizes for the upload rows: the size as a row shows it, and the size limit of uploads with its message.
 * Internal (a subdirectory of `vue/`, not registered).
 *
 * @since 1.20
 */
import { getConfig, i18n } from '@humhub/vue';

const SIZE_UNITS = ['B', 'KB', 'MB', 'GB', 'TB'];

/**
 * @param {number} bytes
 * @returns {string} e.g. "2 KB", "3.5 MB" (steps of 1024, one decimal from KB on)
 */
export function formatFileSize(bytes) {
    let value = Number(bytes) || 0;
    let unit = 0;

    while (value >= 1024 && unit < SIZE_UNITS.length - 1) {
        value /= 1024;
        unit++;
    }

    const formatted = value.toLocaleString(undefined, { maximumFractionDigits: unit === 0 ? 0 : 1 });

    return `${formatted} ${SIZE_UNITS[unit]}`;
}

/**
 * @returns {number} the largest file in bytes the server takes (the core JS config's `file.upload.maxFileSize`),
 *   0 if unknown
 */
export function maxFileSize() {
    const upload = (getConfig('file') || {}).upload || {};

    return Number(upload.maxFileSize) || 0;
}

/**
 * @param {number} [limit] the limit in bytes, if known
 * @returns {string} why a file is refused for its size
 */
export function tooLargeMessage(limit = maxFileSize()) {
    return limit > 0
        ? i18n.t('FileModule.base', 'The file is too large. Maximum: {size}', { size: formatFileSize(limit) })
        : i18n.t('FileModule.base', 'The file is too large.');
}
