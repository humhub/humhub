/**
 * Pure helpers of the filter kit (`FilterBar.vue`, `CardDirectory.vue`): the default value of a
 * filter definition (see `humhub\components\listing\FilterDefinition`), and the translation
 * between filter values, the page URL's query string and the endpoint's request parameters.
 *
 * Value types: `text`/`select` a string (`''` = no filter), `picker`/`tags` a string, or with
 * `multiple` an array of strings, `checkbox` a boolean; a registered filter type (see
 * `registerFilterType()`) a string, or with `multiple` an array of strings. On the wire,
 * arrays are comma-separated and booleans are `1`/`0`; the page URL may carry an array
 * comma-separated or repeated (`tag=a&tag=b`).
 *
 * @since 1.20
 */

const SINGLE_VALUE_TYPES = ['text', 'select', 'checkbox'];

const isMultiple = (filter) => filter.multiple === true && !SINGLE_VALUE_TYPES.includes(filter.type);

const toArray = (value) => (Array.isArray(value)
    ? value.map(String)
    : String(value).split(',').filter((part) => part !== ''));

export const defaultValue = (filter) => {
    if (filter.default !== undefined && filter.default !== null) {
        if (filter.type === 'checkbox') {
            return filter.default === true || filter.default === 1 || filter.default === '1';
        }
        return isMultiple(filter) ? toArray(filter.default) : String(filter.default);
    }
    if (filter.type === 'checkbox') {
        return false;
    }
    return isMultiple(filter) ? [] : '';
};

export const defaultValues = (filters) => Object.fromEntries(filters.map((filter) => [filter.key, defaultValue(filter)]));

export const parseValue = (filter, raw) => {
    if (filter.type === 'checkbox') {
        return raw === '1' || raw === 'true';
    }
    return isMultiple(filter) ? toArray(raw) : String(raw);
};

export const serializeValue = (filter, value) => {
    if (filter.type === 'checkbox') {
        return value ? '1' : '0';
    }
    return Array.isArray(value) ? value.join(',') : String(value ?? '');
};

export const isDefault = (filter, value) => serializeValue(filter, value) === serializeValue(filter, defaultValue(filter));

/**
 * The value without what the filter cannot offer: a `select`/`picker`/`tags` filter with static `options`
 * and no `optionsUrl` (whose options are only known once loaded) knows every value it takes, so
 * a value that is none of them — a stale link, an option that was switched off since — falls
 * back to the default (`tags` with `multiple`: the unknown entries are dropped). The empty value
 * always stays.
 */
export const sanitizeValue = (filter, value) => {
    if (!['select', 'picker', 'tags'].includes(filter.type) || filter.optionsUrl || !Array.isArray(filter.options)) {
        return value;
    }
    const known = new Set(filter.options.map((option) => String(option.value)));
    if (isMultiple(filter)) {
        return toArray(value ?? []).filter((entry) => known.has(entry));
    }
    const serialized = String(value ?? '');
    return serialized === '' || known.has(serialized) ? value : defaultValue(filter);
};

/**
 * {@see sanitizeValue()} for every filter of `values`; other keys are kept as they are.
 */
export const sanitizeValues = (filters, values) => {
    const sanitized = { ...values };
    for (const filter of filters) {
        if (filter.key in sanitized) {
            sanitized[filter.key] = sanitizeValue(filter, sanitized[filter.key]);
        }
    }
    return sanitized;
};

/**
 * The values the page URL carries: every filter present in the query string parsed (and
 * sanitized, see {@see sanitizeValue()}), every other one taken from `base` (default: the
 * filters' defaults).
 */
export const readValues = (filters, search, base = defaultValues(filters)) => {
    const params = new URLSearchParams(search);
    const values = { ...base };
    for (const filter of filters) {
        if (params.has(filter.key)) {
            // A multiple filter's values may be repeated (`tag=a&tag=b`) as well as comma-separated.
            const raw = isMultiple(filter) ? params.getAll(filter.key).join(',') : params.get(filter.key);
            values[filter.key] = sanitizeValue(filter, parseValue(filter, raw));
        }
    }
    return values;
};

/**
 * The query string for the page URL: non-default values set, defaults removed, parameters that
 * are no filter (`r=` without pretty URLs, anything else) kept as they are.
 */
export const writeQuery = (filters, values, search) => {
    const params = new URLSearchParams(search);
    for (const filter of filters) {
        const value = values[filter.key];
        if (isDefault(filter, value)) {
            params.delete(filter.key);
        } else {
            params.set(filter.key, serializeValue(filter, value));
        }
    }
    const query = params.toString();
    return query === '' ? '' : `?${query}`;
};

/**
 * The value of a `fixed` entry (see `FilterBar`'s `fixed` prop) on the wire: arrays
 * comma-separated, booleans `1`/`0`, anything else as a string.
 */
export const serializeFixed = (value) => {
    if (typeof value === 'boolean') {
        return value ? '1' : '0';
    }
    return Array.isArray(value) ? value.join(',') : String(value ?? '');
};

/**
 * A comparable form of a whole `fixed` object: its entries as sent, in key order — equal for two
 * objects that send the same values, whatever their identity or key order (an inline
 * `:fixed="{…}"` is a new object with every render of its parent).
 */
export const fixedSignature = (fixed) => JSON.stringify(Object.keys(fixed || {}).sort().map((key) => [key, serializeFixed(fixed[key])]));

/**
 * The endpoint parameters: every filter with a value — a default that is not "empty" (a `sort`
 * default, a checkbox) is sent too, so the endpoint never has to know the page's defaults —
 * and every `fixed` value, which overrides a filter of the same key.
 */
export const requestParams = (filters, values, fixed = {}) => {
    const params = {};
    for (const filter of filters) {
        const serialized = serializeValue(filter, values[filter.key]);
        if (filter.type === 'checkbox' || serialized !== '') {
            params[filter.key] = serialized;
        }
    }
    for (const [key, value] of Object.entries(fixed || {})) {
        params[key] = serializeFixed(value);
    }
    return params;
};
