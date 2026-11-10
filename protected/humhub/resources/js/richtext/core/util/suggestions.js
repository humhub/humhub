/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * A list of suggestions for the text being typed (a mention, an emoji): a named `listbox` of `option`s, whose current
 * option the editor points to with `aria-activedescendant`, so a screen reader reads it while the focus stays in the
 * text (WCAG 4.1.2).
 */
let count = 0;

export function suggestionListId() {
    return 'richtext-suggestions-' + (++count);
}

/**
 * @param {EditorView|null} view the editor
 * @param {string|null} listId the listbox, `null` once it is closed
 * @param {string|null} optionId the current option
 */
export function setCurrentSuggestion(view, listId, optionId) {
    const dom = view && view.dom;
    if (!dom) {
        return;
    }

    if (listId && optionId) {
        dom.setAttribute('aria-autocomplete', 'list');
        dom.setAttribute('aria-controls', listId);
        dom.setAttribute('aria-activedescendant', optionId);
    } else {
        dom.removeAttribute('aria-autocomplete');
        dom.removeAttribute('aria-controls');
        dom.removeAttribute('aria-activedescendant');
    }
}
