/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {InputRule} from 'prosemirror-inputrules';

/**
 * Text shortcuts replaced by an emoji while typing, e.g. `:)`
 */
export const shortcuts = {
    '😠': ['>:(', '>:-('],
    '😊': [':")', ':-")'],
    '💔': ['</3', '<\\3'],
    '😕': [':/', ':-/'],
    '😢': [":'(", ":'-(", ':,(', ':,-('],
    '😦': [':(', ':-('],
    '❤️': ['<3'],
    '👿': [']:(', ']:-('],
    '😇': ['o:)', 'O:)', 'o:-)', 'O:-)', '0:)', '0:-)'],
    '😂': [":')", ":'-)", ':,)', ':,-)', ":'D", ":'-D", ':,D', ':,-D'],
    '😗': [':*', ':-*'],
    '😆': ['x-)', 'X-)'],
    '😐': [':|', ':-|'],
    '😮': [':o', ':-o', ':O', ':-O'],
    '😡': [':@', ':-@'],
    '😄': [':D', ':-D'],
    '🙂': [':)', ':-)'],
    '😈': [']:)', ']:-)'],
    '😭': [":,'(", ":,'-(", ';(', ';-('],
    '😛': [':P', ':-P'],
    '😎': ['8-)', 'B-)'],
    '😓': [',:(', ',:-('],
    '😅': [',:)', ',:-)'],
    '😒': [':s', ':-S', ':z', ':-Z', ':$', ':-$'],
    '😉': [';)', ';-)'],
};

const quote = (str) => str.replace(/[.?*+^$[\]\\(){}|-]/g, '\\$&');

/**
 * A shortcut is replaced when it is typed after a space or at the start of a line. Shortcuts ending with a letter
 * (`:D`, `:o`) only once a space follows, so typing `:dog` still starts the autocomplete.
 */
export function shortcutRules() {
    const byShortcut = {};
    Object.keys(shortcuts).forEach((emoji) => {
        shortcuts[emoji].forEach((shortcut) => {
            byShortcut[shortcut] = emoji;
        });
    });

    const all = Object.keys(byShortcut).sort((a, b) => b.length - a.length);
    const immediate = all.filter((shortcut) => !/[a-z0-9]$/i.test(shortcut)).map(quote).join('|');
    const delayed = all.filter((shortcut) => /[a-z0-9]$/i.test(shortcut)).map(quote).join('|');

    const rule = (regex, suffix) => new InputRule(regex, (state, match, start, end) => {
        const emoji = byShortcut[match[2]];
        if (!emoji || state.doc.resolve(start).marks().some((mark) => mark.type.spec.isCode)) {
            return null;
        }

        return state.tr.insertText(emoji + suffix, start + match[1].length, end);
    });

    return [
        rule(new RegExp('(^|\\s)(' + immediate + ')$'), ''),
        rule(new RegExp('(^|\\s)(' + delayed + ')\\s$'), ' '),
    ];
}
