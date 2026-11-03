import { client, getPickerSource, log } from '@humhub/vue';
import { toImage } from './images.js';

/**
 * Picker sources (see docs/develop/ui-js-vuejs-picker.md, "Sources"): a source is
 * `{ search(term) → Promise<item[]>, remote, newImage }`, `remote` telling whether it asks a server
 * (debounced, `minInput` applies) or filters what it holds, `newImage` the image of an item
 * added with `allowNew` (a named source may give one, else none).
 *
 * @since 1.20
 */

const fold = (text) => String(text).normalize('NFD').replace(/\p{M}/gu, '').toLocaleLowerCase();

/**
 * Whether `text` contains `term`, ignoring case and accents.
 */
export const matches = (text, term) => term === '' || fold(text).includes(fold(term));

/**
 * The internal item of any raw item: `{ id, text, subText, image, disabled, locked }`, `id` a
 * string read from the `itemKey` field, else `id`. Reads the legacy shape (`text`, `title`,
 * `displayName`; `subText`, `displayNameSub`).
 */
export const normalizeItem = (raw, itemKey = 'id') => ({
    id: String(raw[itemKey] ?? raw.id),
    text: String(raw.text ?? raw.title ?? raw.displayName ?? raw.name ?? raw.id),
    subText: raw.subText ?? raw.displayNameSub ?? null,
    image: toImage(raw.image),
    disabled: raw.disabled === true,
    locked: raw.locked === true,
});

// Malformed answers: an item with neither the item key field nor `id` is dropped.
const hasKey = (raw, itemKey) => !!raw && (raw[itemKey] ?? raw.id) !== undefined && (raw[itemKey] ?? raw.id) !== null;

/**
 * A source over a fixed list of items, filtered in the browser.
 */
export const staticSource = (items) => {
    const all = items.map((item) => normalizeItem(item));
    return {
        remote: false,
        newImage: null,
        search: (term) => Promise.resolve(all.filter((item) => matches(item.text, term))),
    };
};

const answerList = (response) => {
    if (Array.isArray(response)) {
        return response;
    }
    if (response && Array.isArray(response.data)) {
        return response.data;
    }
    if (response && Array.isArray(response.results)) {
        return response.results;
    }
    return [];
};

/**
 * A source over a legacy picker endpoint: `GET <url>&keyword=<term>` answering
 * `[{ id, text, subText?, image?, disabled? }]`.
 */
export const legacySource = (url, itemKey = 'id') => ({
    remote: true,
    newImage: null,
    search: (term) => client.get(`${url}${url.includes('?') ? '&' : '?'}keyword=${encodeURIComponent(term)}`)
        .then((response) => answerList(response).filter((raw) => hasKey(raw, itemKey)).map((raw) => normalizeItem(raw, itemKey))),
});

/**
 * The source of a picker: the named `source` registered with `registerPickerSource()`, else
 * the legacy `url`, else the static `options` (also when the named source is not registered —
 * its module's bundle is missing —, with a warning).
 */
export const createSource = ({ source = null, url = null, options = [], itemKey = 'id', params = {} } = {}) => {
    if (source) {
        const factory = getPickerSource(source);
        if (factory) {
            const named = factory({ itemKey, params });
            return {
                remote: true,
                newImage: named.newImage ?? null,
                search: (term) => Promise.resolve(named.search(term)).then((items) => (Array.isArray(items) ? items : []).filter((item) => hasKey(item, itemKey)).map((item) => normalizeItem(item, itemKey))),
            };
        }
        log.warn(`Picker source "${source}" is not registered — the picker offers its static options only`);
        return staticSource(options);
    }

    return url ? legacySource(url, itemKey) : staticSource(options);
};
