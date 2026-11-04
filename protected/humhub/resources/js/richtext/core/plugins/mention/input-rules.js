import {InputRule} from "prosemirror-inputrules";
import {TextSelection} from "prosemirror-state";
import {hasMark} from "../../util/node";

// https://github.com/ProseMirror/prosemirror/issues/262
const objectReplacementCharacter = '\ufffc';

let mentionRule = (schema) => {
    // eslint-disable-next-line
    return new InputRule(new RegExp('(^|[\\s\(' + objectReplacementCharacter + '])@$'), (state, match, start, end) => {
        const mark = schema.mark('mentionQuery');
        const mentionText = schema.text('@', [mark]);

        // Prevents an error log when using IME
        if (hasMark(state.selection.$anchor.nodeBefore, mark)) {
            return;
        }

        start = start + (match[0].length - 1);

        return state.tr
            .removeMark(0, state.doc.nodeSize - 2, mark)
            .setSelection(TextSelection.create(state.doc, start, end))
            .replaceSelectionWith(mentionText, false);
    });
};

export {mentionRule};
