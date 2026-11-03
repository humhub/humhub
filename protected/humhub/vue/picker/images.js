import { log } from '@humhub/vue';

/**
 * Reads a picker item's image into an image description (see
 * docs/develop/ui-js-vuejs-picker.md, "Images"): `{ type, …fields }`, or `null` for none.
 *
 * - A description (an object with a `type`) is taken as is; a JSON string of one too (the
 *   `data-image` attribute of a server-rendered `<option>`).
 * - A string is read as the Select2 picker read it: `/…` or `http…` → `url`, `#…` → `color`,
 *   `ti-<name>` → `icon` `<name>`, `fa-<name>` → `icon` `fa-<name>` (rendered by the Font
 *   Awesome compatibility layer), any other word → `icon`.
 * - HTML (a string containing `<`) is never rendered: `null`, with a warning.
 *
 * @since 1.20
 */
export const toImage = (value) => {
    if (value === null || value === undefined || value === '') {
        return null;
    }

    if (typeof value === 'object') {
        return typeof value.type === 'string' && value.type !== '' ? value : null;
    }

    const text = String(value).trim();

    if (text === '') {
        return null;
    }

    if (text.startsWith('{')) {
        try {
            return toImage(JSON.parse(text));
        } catch (error) {
            return null;
        }
    }

    if (text.includes('<')) {
        log.warn('Picker item image given as HTML is not rendered — give an image description instead', text);
        return null;
    }

    if (text.startsWith('/') || text.startsWith('http')) {
        return { type: 'url', url: text };
    }

    if (text.startsWith('#')) {
        return { type: 'color', color: text };
    }

    if (text.startsWith('ti-')) {
        return { type: 'icon', name: text.slice(3) };
    }

    return { type: 'icon', name: text };
};
