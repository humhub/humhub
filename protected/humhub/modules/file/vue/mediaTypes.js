/**
 * The media types of attached files, by file extension: shared by `AttachedFiles.vue` (which file goes where) and
 * `components/AttachedMedia.vue` (the MIME type of a video tile).
 *
 * @since 1.20
 */

// The media viewer needs the type, otherwise it shows a video as an image
export const VIDEO_TYPES = {
    webm: 'video/webm',
    mp4: 'video/mp4',
    ogv: 'video/ogg',
    mov: 'video/quicktime',
};
export const VIDEO_EXTENSIONS = Object.keys(VIDEO_TYPES);
// Formats every current browser plays in `<audio>` - Ogg is left out, Safari's support is unreliable
export const AUDIO_EXTENSIONS = ['mp3', 'wav', 'm4a', 'aac', 'flac'];

/**
 * @param {{fileName?: string}} file
 * @returns {string} the lower-case extension, empty if there is none
 */
export function extensionOf(file) {
    const name = String(file.fileName || '');
    const dot = name.lastIndexOf('.');

    return dot === -1 ? '' : name.slice(dot + 1).toLowerCase();
}
