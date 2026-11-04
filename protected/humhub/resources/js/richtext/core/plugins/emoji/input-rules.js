import {InputRule} from "prosemirror-inputrules";
import {TextSelection} from "prosemirror-state";
import {getEmojiDefinitionByShortcut, shortcuts} from "./util";
import {hasMark} from "../../util/node";
import {isSmallView} from "../../humhub-bridge";

// https://github.com/ProseMirror/prosemirror/issues/262
const objectReplacementCharacter = '\ufffc';

function quoteRE(str) {
    return str.replace(/[.?*+^$[\]\\(){}|-]/g, '\\$&');
}

// all emoji shortcuts in string seperated by |
let shortcutStr = Object.keys(shortcuts)
    .sort()
    .reverse()
    .map(function (shortcut) {
        return quoteRE(shortcut);
    })
    .join('|');

let scanRE = new RegExp('(?:^|\\ )(' + shortcutStr + ')$');

let emojiAutoCompleteRule = function (schema) {
    return new InputRule(scanRE, function (state, match, start, end) {
        // Only handle match if match is at the end of the match input
        if (match.index !== (match.input.length - match[0].length)) {
            return false;
        }

        // Match e.g. :) => smiley
        let emojiDef = getEmojiDefinitionByShortcut(match[1]);
        if (emojiDef.name && emojiDef.emoji && emojiDef.$dom) {
            let node = state.schema.nodes.emoji.create({
                'data-name': emojiDef.name,
                alt: emojiDef.$dom.attr('alt'),
                src: emojiDef.$dom.attr('src')
            });

            start = start + (match[0].length - match[1].length);

            return state.tr.delete(start, end).replaceSelectionWith(node, false);
        }

        return false;
    })
};

let emojiChooser = function (schema) {
    return new InputRule(new RegExp('(^|\\ +)(:$)'), function (state, match, start, end) {
        if (isSmallView()) {
            return;
        }

        const mark = schema.mark('emojiQuery');
        const emojiText = schema.text(':', [mark]);

        // Prevents an error log when using IME
        if (hasMark(state.selection.$anchor.nodeBefore, mark)) {
            return;
        }

        start = start + (match[0].length - 1);

        return state.tr
            .removeMark(0, state.doc.nodeSize - 2, mark)
            .setSelection(TextSelection.create(state.doc, start, end))
            .replaceSelectionWith(emojiText, false);
    })
};

export {emojiAutoCompleteRule, emojiChooser}
