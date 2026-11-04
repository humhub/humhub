import {InputRule} from "prosemirror-inputrules"

// : (NodeType) → InputRule
// Given a blockquote node type, returns an input rule that turns `"> "`
// at the start of a textblock into a blockquote.
const strongRule = (schema) => {
    // eslint-disable-next-line
    return markInputRule(/(?:\*\*|__)([^\*_]+)(?:\*\*|__)$/, schema.marks.strong);
};

function hasCodeMark(node)
{
    if (!node) {
        return false;
    }

    let result = false;
    node.marks.forEach((mark) => {
        if (mark.type.spec.isCode) {
            result = true;
        }
    });

    return result;
}

function markInputRule(regexp, markType, getAttrs) {
    return new InputRule(regexp, (state, match, start, end) => {
        let attrs = getAttrs instanceof Function ? getAttrs(match) : getAttrs;
        let nodeBeforeEnd = state.selection.$to.nodeBefore;

        if (!nodeBeforeEnd || !nodeBeforeEnd.isText
            || nodeBeforeEnd.text.length < match[0].length - 1 // check that the match does not span multiple nodes
            || hasCodeMark(nodeBeforeEnd)
            || markType.isInSet(nodeBeforeEnd.marks)) {
            return null;
        }

        if (match[1]) {
            let tr = state.tr;
            let textStart = start + match[0].indexOf(match[1]);
            let textEnd = textStart + match[1].length;
            if (textEnd < end) tr.delete(textEnd, end);
            if (textStart > start) tr.delete(start, textStart);
            end = start + match[1].length;
            tr.addMark(start, end, markType.create(attrs));
            tr.removeStoredMark(markType); // Do not continue with mark.
            return tr;
        }

        return null;
    });
}

export {strongRule};
