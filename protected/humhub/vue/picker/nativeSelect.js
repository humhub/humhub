import { toImage } from './images.js';

/**
 * The native `<select>` under a `PickerInput` (see docs/develop/ui-js-vuejs-picker.md, "Markup
 * contract" and "Native bridge"): its settings, its options and selection, and writing a
 * selection back.
 *
 * @since 1.20
 */

const NUMBERS = ['maxSelection', 'suggested', 'minInput', 'maxInput', 'maxResults', 'search'];
const BOOLEANS = ['allowNew', 'allowClear', 'listed', 'required'];
const STRINGS = ['source', 'url', 'itemKey', 'placeholder', 'placeholderMore', 'label', 'newPrefix'];

const camel = (name) => name.replace(/-([a-z])/g, (all, letter) => letter.toUpperCase());

const readBoolean = (value) => value === '' || value === 'true' || value === '1';

// A number attribute, `undefined` when empty or not a number (the setting is then left out).
const readNumber = (value) => {
    if (value === null || value === undefined || String(value).trim() === '') {
        return undefined;
    }
    const number = Number(value);
    return Number.isNaN(number) ? undefined : number;
};

/**
 * The `<label for>` of `select`, else the label wrapping it, `null` for none.
 */
export const labelOf = (select) => {
    const labels = [...(select.labels || [])];
    return labels.find((label) => !label.contains(select)) || labels[0] || select.closest('label');
};

/**
 * The text of `label`: a wrapping label without the select (its options) and the picker after it.
 */
export const labelText = (label, select) => {
    if (!label.contains(select)) {
        return label.textContent.trim();
    }
    const copy = label.cloneNode(true);
    copy.querySelectorAll('select, .picker-host').forEach((node) => node.remove());
    return copy.textContent.replace(/\s+/g, ' ').trim();
};

/**
 * An `<option>` as item: `{ id, text, subText, image, disabled, locked, group }`.
 */
export const readOption = (option) => {
    const image = option.dataset.image ?? (option.dataset.color ? { type: 'color', color: option.dataset.color } : null);
    return {
        id: option.value,
        text: option.dataset.text ?? option.textContent.trim(),
        subText: option.dataset.subText ?? null,
        image: typeof image === 'string' && image.trim().startsWith('{') ? toImage(image) : image,
        disabled: option.disabled,
        locked: option.hasAttribute('data-locked'),
        group: option.parentElement && option.parentElement.tagName === 'OPTGROUP' ? option.parentElement.label : null,
    };
};

/**
 * The chosen items, in option order.
 */
export const readSelection = (select) => [...select.options].filter((option) => option.selected && option.value !== '').map(readOption);

/**
 * The `PickerInput` props a `<select>` describes.
 */
export const readConfig = (select) => {
    const config = {};
    let json = {};
    try {
        json = select.dataset.pickerConfig ? JSON.parse(select.dataset.pickerConfig) : {};
    } catch (error) {
        json = {};
    }
    Object.assign(config, json);

    // Aliases of the Select2 era (data-ui-select2), read until 1.21.
    const minimumResultsForSearch = readNumber(select.getAttribute('data-minimum-results-for-search'));
    if (minimumResultsForSearch !== undefined) {
        config.search = minimumResultsForSearch;
    }
    if (select.hasAttribute('data-ui-select2-allow-new')) {
        config.allowNew = true;
    }

    Object.keys(select.dataset).forEach((key) => {
        if (!key.startsWith('picker') || key === 'pickerConfig' || key === 'pickerBound') {
            return;
        }
        const name = camel(key.slice('picker'.length).replace(/^./, (letter) => letter.toLowerCase()));
        const value = select.dataset[key];
        if (NUMBERS.includes(name)) {
            const number = readNumber(value);
            if (number !== undefined) {
                config[name] = number;
            }
        } else if (BOOLEANS.includes(name)) {
            config[name] = readBoolean(value);
        } else if (STRINGS.includes(name)) {
            config[name] = value;
        }
    });

    const hasSource = !!(config.source || config.url);
    const selection = readSelection(select);
    const label = labelOf(select);

    config.multiple = select.multiple;
    config.disabled = select.disabled;
    config.invalid = select.classList.contains('is-invalid');
    config.required = config.required || select.required || select.getAttribute('aria-required') === 'true';
    config.autofocus = select.autofocus;
    config.label = config.label || (label ? labelText(label, select) : '');
    config.options = hasSource ? [] : [...select.options].map(readOption);
    config.initialItems = selection;
    config.modelValue = select.multiple ? selection.map((item) => item.id) : (selection[0]?.id ?? '');
    config.allowClear = config.allowClear ?? [...select.options].some((option) => option.value === '');
    config.name = null;
    return config;
};

/**
 * After a `form.reset()` of a picker: drops the options it added since (neither selected nor
 * initially selected) and puts the remaining ones back in their initial order (`initialValues`,
 * the option values when the picker was created).
 */
export const pruneAfterReset = (select, initialValues) => {
    [...select.options].filter((option) => !option.selected && !option.defaultSelected).forEach((option) => option.remove());
    const position = (option) => {
        const index = initialValues.indexOf(option.value);
        return index < 0 ? initialValues.length : index;
    };
    [...select.options].sort((a, b) => position(a) - position(b)).forEach((option) => select.appendChild(option));
};

const describeImage = (image) => (image && typeof image === 'object' ? JSON.stringify(image) : null);

/**
 * Makes `items` the selection of `select`: a picker (`prune`) holds only its chosen options
 * (created as needed, in the order chosen) — and its initially selected ones, unselected, so
 * `form.reset()` can restore them —, an enhanced select keeps all options and moves the
 * selection. A single select without items selects its empty option (created as needed), so the
 * empty value is posted. Fires a bubbling `change` when the selection changed.
 */
export const writeSelection = (select, items, { prune }) => {
    const ids = items.map((item) => item.id);
    const before = [...select.selectedOptions].map((option) => option.value).join('\n');
    const byValue = new Map([...select.options].map((option) => [option.value, option]));

    items.forEach((item) => {
        let option = byValue.get(item.id);
        if (!option) {
            option = select.ownerDocument.createElement('option');
            option.value = item.id;
            option.textContent = item.text;
            option.dataset.text = item.text;
            if (item.subText) {
                option.dataset.subText = item.subText;
            }
            const image = describeImage(item.image);
            if (image) {
                option.dataset.image = image;
            }
        }
        if (prune || !option.parentNode) {
            select.appendChild(option);
        }
    });

    [...select.options].forEach((option) => {
        const chosen = ids.includes(option.value);
        if (!chosen && prune && !option.defaultSelected && option.value !== '') {
            option.remove();
            return;
        }
        option.selected = chosen;
    });

    if (!select.multiple && ids.length === 0) {
        let empty = [...select.options].find((option) => option.value === '');
        if (!empty) {
            empty = select.ownerDocument.createElement('option');
            empty.value = '';
            select.prepend(empty);
        }
        empty.selected = true;
    }

    if ([...select.selectedOptions].map((option) => option.value).join('\n') !== before) {
        select.dispatchEvent(new Event('change', { bubbles: true }));
    }
};
