import {
    baseKeymap,
    wrapIn,
    setBlockType,
    chainCommands,
    toggleMark,
    exitCode,
    joinUp,
    joinDown,
    lift,
    selectParentNode,
    splitBlock,
    deleteSelection,
    joinBackward,
    selectNodeBackward,
    newlineInCode,
    createParagraphNear,
    liftEmptyBlock,
} from "prosemirror-commands";
import {wrapInList, splitListItem, liftListItem, sinkListItem} from "prosemirror-schema-list";
import {undo, redo} from "prosemirror-history";
import {undoInputRule} from "prosemirror-inputrules";
import {Selection, TextSelection} from "prosemirror-state";

const mac = typeof navigator != "undefined" ? /Mac/.test(navigator.platform) : false;

function exitCodeAtLast(state, dispatch) {
    let ref = state.selection;
    let $head = ref.$head;
    let $anchor = ref.$anchor;
    let parent = $head.parent;

    let isBlockQuote = false;
    $anchor.path.forEach((item, index) => {
        if (!(index % 3) && item.type && item.type.name === 'blockquote') {
            isBlockQuote = true;
        }
    });

    if (!(parent.type.spec.code || isBlockQuote)
        || $anchor.parentOffset != $head.parentOffset
        || !$head.sameParent($anchor)
        || $head.parent.content.size != $head.parentOffset) {
        return false;
    }

    let nodeAfter = state.doc.resolve($head.pos - $head.parentOffset + parent.nodeSize - 1).nodeAfter;
    if (nodeAfter) {
        return false;
    }

    let above = $head.node(-1);
    let after = $head.indexAfter(-1);
    let type = above.contentMatchAt(after).defaultType;

    if (!above.canReplaceWith(after, after, type)) {
        return false;
    }

    if (dispatch) {
        let pos = (!parent.type.spec.code && isBlockQuote) ? $head.after() + 1 : $head.after();
        let tr = state.tr.replaceWith(pos, pos, type.createAndFill());
        tr.setSelection(Selection.near(tr.doc.resolve(pos), 1));
        dispatch(tr.scrollIntoView());
    }

    return true;
}

function exitMarkAtLast(state, dispatch) {
    let selection = state.selection;
    if (selection instanceof TextSelection
        && !selection.$head.nodeAfter
        && selection.$head.nodeBefore
        && selection.$head.nodeBefore.isText
        && selection.$head.nodeBefore.marks.length) {
        if (dispatch) {
            selection.$head.nodeBefore.marks.forEach((mark) => {
                removeMark(mark.type, state, dispatch);
            });
        }
    }

    return false;
}

function removeMark(markType, state, dispatch) {
    let {empty, $cursor, ranges} = state.selection;
    if ((empty && !$cursor) || !markApplies(state.doc, ranges, markType)) return false;
    if (dispatch) {
        if ($cursor) {
            if (markType.isInSet(state.storedMarks || $cursor.marks()))
                dispatch(state.tr.removeStoredMark(markType));
        } else {
            let has = false, tr = state.tr;
            for (let i = 0; !has && i < ranges.length; i++) {
                let {$from, $to} = ranges[i];
                has = state.doc.rangeHasMark($from.pos, $to.pos, markType);
            }
            for (let i = 0; i < ranges.length; i++) {
                let {$from, $to} = ranges[i];
                if (has) tr.removeMark($from.pos, $to.pos, markType);
            }
            dispatch(tr.scrollIntoView());
        }
    }
    return true;
}

function markApplies(doc, ranges, type) {
    for (let i = 0; i < ranges.length; i++) {
        let {$from, $to} = ranges[i];
        let can = $from.depth == 0 ? doc.type.allowsMarkType(type) : false;
        doc.nodesBetween($from.pos, $to.pos, node => {
            if (can) return false;
            can = node.inlineContent && node.type.allowsMarkType(type);
        })
        if (can) return true;
    }
    return false;
}

// :: (Schema, ?Object) → Object
// Inspect the given schema looking for marks and nodes from the
// basic schema, and if found, add key bindings related to them.
// This will add:
//
// * **Mod-b** for toggling [strong](#schema-basic.StrongMark)
// * **Mod-i** for toggling [emphasis](#schema-basic.EmMark)
// * **Mod-`** for toggling [code font](#schema-basic.CodeMark)
// * **Ctrl-Shift-0** for making the current textblock a paragraph
// * **Ctrl-Shift-1** to **Ctrl-Shift-Digit6** for making the current
//   textblock a heading of the corresponding level
// * **Ctrl-Shift-Backslash** to make the current textblock a code block
// * **Ctrl-Shift-8** to wrap the selection in an ordered list
// * **Ctrl-Shift-9** to wrap the selection in a bullet list
// * **Ctrl->** to wrap the selection in a block quote
// * **Enter** to split a non-empty textblock in a list item while at
//   the same time splitting the list item
// * **Mod-Enter** to insert a hard break
// * **Mod-_** to insert a horizontal rule
// * **Backspace** to undo an input rule
// * **Alt-ArrowUp** to `joinUp`
// * **Alt-ArrowDown** to `joinDown`
// * **Mod-BracketLeft** to `lift`
// * **Escape** to `selectParentNode`
//
// You can suppress or map these bindings by passing a `mapKeys`
// argument, which maps key names (say `"Mod-B"` to either `false`, to
// remove the binding, or a new key name string.
export function buildKeymap(context) {
    let keys = {}, type;

    let schema = context.schema;
    let mapKeys = context.options.mapKeys;

    function bind(key, cmd) {
        if (mapKeys) {
            let mapped = mapKeys[key];
            if (mapped === false) return;
            if (mapped) key = mapped;
        }
        keys[key] = cmd;
    }

    bind("Escape", selectParentNode);
    bind('ArrowDown', exitCodeAtLast);
    bind('ArrowRight', exitMarkAtLast);

    bind("Alt-ArrowUp", joinUp);
    bind("Alt-ArrowDown", joinDown);
    bind("Mod-BracketLeft", lift);

    bind("Mod-z", undo);
    bind("Shift-Mod-z", redo);

    if (!mac) bind("Mod-y", redo);

    if (type = schema.marks.strong)
        bind("Mod-b", toggleMark(type));
    if (type = schema.marks.em)
        bind("Mod-i", toggleMark(type));
    if (type = schema.marks.code)
        bind("Mod-`", toggleMark(type));

    if (type = schema.nodes.bullet_list)
        bind("Shift-Ctrl-8", wrapInList(type));
    if (type = schema.nodes.ordered_list)
        bind("Shift-Ctrl-9", wrapInList(type));
    if (type = schema.nodes.blockquote)
        bind("Ctrl->", wrapIn(type));
    if (type = schema.nodes.hard_break) {
        let br = type, cmd = chainCommands(exitCode, (state, dispatch) => {
            if (state.selection
                && state.selection.$anchor.parent
                && state.selection.$anchor.parent.type === schema.nodes.heading) {
                splitBlock(state, dispatch);
                return true;
            }
            dispatch(state.tr.replaceSelectionWith(br.create()).scrollIntoView());
            return true;
        });
        bind("Mod-Enter", cmd);
        bind("Shift-Enter", cmd);
        if (mac) bind("Ctrl-Enter", cmd);
    }

    let splitList;

    if (type = schema.nodes.list_item) {
        splitList = splitListItem(type);
        bind("Mod-[", liftListItem(type));
        bind("Mod-]", sinkListItem(type));
    }
    if (type = schema.nodes.paragraph)
        bind("Shift-Ctrl-0", setBlockType(type));
    if (type = schema.nodes.code_block)
        bind("Shift-Ctrl-\\", setBlockType(type));
    if (type = schema.nodes.heading)
        for (let i = 1; i <= 6; i++)
            bind("Shift-Ctrl-" + i, setBlockType(type, {level: i}));
    if (type = schema.nodes.horizontal_rule) {
        let hr = type;
        bind("Mod-_", (state, dispatch) => {
            dispatch(state.tr.replaceSelectionWith(hr.create()).scrollIntoView());
            return true;
        })
    }

    baseKeymap['Backspace'] = chainCommands(undoInputRule, deleteSelection, joinBackward, selectNodeBackward);

    if (splitList) {
        baseKeymap['Enter'] = chainCommands(splitList, newlineInCode, createParagraphNear, liftEmptyBlock, splitBlock);
    } else {
        baseKeymap['Enter'] = chainCommands(newlineInCode, createParagraphNear, liftEmptyBlock, splitBlock);
    }

    for (const key in baseKeymap) {
        bind(key, baseKeymap[key]);
    }

    return keys;
}
