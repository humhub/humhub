import {InputRule} from "prosemirror-inputrules"

const codeRules = (schema) => {
    return [
        markInputRuleOpen(/(?:`)([^`]+)$/, schema.marks.code),
        markInputRuleClosed(/(?:`)([^`]+)(?:`)$/, schema.marks.code)
    ]
};

function markInputRuleOpen(regexp, markType, getAttrs) {
    return new InputRule(regexp, (state, match, start, end) => {
        let attrs = getAttrs instanceof Function ? getAttrs(match) : getAttrs;
        let tr = state.tr;

        const nodeAfter = state.selection.$to.nodeAfter;
        if (nodeAfter && nodeAfter.isText && nodeAfter.text.indexOf('`') === 0) {
            tr.delete(start, end + 1);
            tr.addStoredMark(markType.create(attrs));
            tr.insertText(match[1], start);
            return tr;
        }

        return null;
    })
}

function markInputRuleClosed(regexp, markType, getAttrs) {
    return new InputRule(regexp, (state, match, start, end) => {
        let attrs = getAttrs instanceof Function ? getAttrs(match) : getAttrs;
        let tr = state.tr;

        if (match[1]) {
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
    })
}

export {codeRules};
