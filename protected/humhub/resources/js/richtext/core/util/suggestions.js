/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {announce} from "../humhub-bridge";
import {translate} from "../menu/menu-helper";

/**
 * A list of suggestions for the text being typed (a mention, an emoji): a named `listbox` of `option`s, whose current
 * option the editor points to with `aria-activedescendant`, so a screen reader reads it while the focus stays in the
 * text (WCAG 4.1.2). How many there are is said when the list opens or their number changes (WCAG 4.1.3).
 *
 * The editor stays a `textbox`: a multi-line text is no `combobox`, and a `textbox` has no `aria-expanded`.
 */
let count = 0;

// The number of suggestions last said, per editor
const announced = new WeakMap();

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

/**
 * The list is closed: its number is said again when a new one opens
 *
 * @param {EditorView|null} view the editor
 */
export function closeSuggestions(view) {
    setCurrentSuggestion(view, null, null);
    if (view) {
        announced.delete(view);
    }
}

/**
 * Says how many suggestions there are ("Suggestions: 3"), or that there are none, once per number
 *
 * @param {EditorView|null} view the editor
 * @param {number} number
 */
export function announceSuggestions(view, number) {
    if (!view || announced.get(view) === number) {
        return;
    }

    announced.set(view, number);
    announce(number > 0 ? translate(view, 'Suggestions') + ': ' + number : translate(view, 'No results'));
}
