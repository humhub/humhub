import {joinUp, selectParentNode, wrapIn, setBlockType, toggleMark} from "prosemirror-commands";
import {undo, redo} from "prosemirror-history";
import {liftTarget} from "prosemirror-transform";
import {wrapInList} from "prosemirror-schema-list";

import {icons} from "./icons";
import {Dropdown} from "./dropdown";
import {DropdownSubmenu} from "./dropdown-sub";
import {MenuItem} from "./menuitem";
import {MenuItemGroup} from "./menugroup";

export {icons} from "./icons";
export {Dropdown} from "./dropdown";
export {DropdownSubmenu} from "./dropdown-sub";
export {MenuItem} from "./menuitem";
export {MenuItemGroup} from "./menugroup";

// Helpers to create specific types of items
export function cmdItem(cmd, options) {
    let passedOptions = {
        label: options.title,
        run: cmd
    };
    for (let prop in options) passedOptions[prop] = options[prop]
    if ((!options.enable || options.enable === true) && !options.select)
        passedOptions[options.enable ? "enable" : "select"] = state => cmd(state)

    return new MenuItem(passedOptions)
}

export function markItem(markType, options, context) {
    let passedOptions = {
        active(state) {
            return markActive(state, markType)
        },
        enable: true
    };
    for (let prop in options) passedOptions[prop] = options[prop]
    let menuItem = cmdItem(toggleMark(markType), passedOptions)

    if (options.runSource) {
        menuItem.runSource = options.runSource;
    } else if (context && markType.spec.toMarkdown
        && typeof markType.spec.toMarkdown.open === 'string'
        && typeof markType.spec.toMarkdown.close === 'string') {
        menuItem.runSource = wrapSourceTextMark(context, markType);
    }

    return menuItem;
}

export function wrapSourceTextMark(context, markType, open, close) {
    if (!open && markType.spec.toMarkdown && markType.spec.toMarkdown.open) {
        open = markType.spec.toMarkdown.open;
    }

    if (!close && markType.spec.toMarkdown && markType.spec.toMarkdown.close) {
        close = markType.spec.toMarkdown.close;
    }

    if (!open) {
        return;
    }

    if (!close) {
        close = open;
    }

    return function () {
        let $source = context.$source;
        const length = $source.val().length;
        const start = $source[0].selectionStart;
        const selectionDirection = $source[0].selectionDirection
        const end = $source[0].selectionEnd;
        let selectedText = $source.val().substring(start, end);

        const preSelection = $source.val().substring((start - open.length), start);
        const postSelection = $source.val().substring(end, (end + close.length));

        if (preSelection === open && postSelection === close) {
            // Revert mark
            $source.val($source.val().substring(0, start - open.length) + selectedText + $source.val().substring(end + close.length, length));
            $source[0].setSelectionRange((start - open.length), end - open.length, selectionDirection);
        } else {
            let leadingSpaceCount = Math.max(selectedText.search(/\S/), 0);
            let leadingSpaces = leadingSpaceCount > 0
                ? ' '.repeat(leadingSpaceCount)
                : '';

            let trailingSpaceCount = selectedText.search(/ +$/) > -1
                ? selectedText.length - selectedText.search(/ +$/)
                : 0;
            let trailingSpaces = trailingSpaceCount > 0
                ? ' '.repeat(trailingSpaceCount)
                : '';

            selectedText = selectedText.trim();

            const replacement = open + selectedText + close;
            $source.val($source.val().substring(0, start) + leadingSpaces + replacement + trailingSpaces + $source.val().substring(end, length));
            $source[0].setSelectionRange((start + leadingSpaceCount + open.length), (end + open.length - trailingSpaceCount), selectionDirection);
        }
    }
}

export function markActive(state, type) {
    let {from, $from, to, empty} = state.selection
    if (empty) return type.isInSet(state.storedMarks || $from.marks())
    else return state.doc.rangeHasMark(from, to, type)
}

export function wrapListItem(nodeType, options) {
    return cmdItem(wrapInList(nodeType, options.attrs), options)
}

// :: MenuItem
// Menu item for the `joinUp` command.
export const joinUpItem = function () {
    return new MenuItem({
        title: "Join with above block",
        run: joinUp,
        select: state => joinUp(state),
        icon: icons.join
    });
};

// :: MenuItem
// Menu item for the `lift` command.
export const liftItem = function () {
    return new MenuItem({
        title: "Lift out of enclosing block",
        run: lift,
        select: state => lift(state),
        icon: icons.outdent
    });
};

function lift(state, dispatch) {
    const ref = state.selection;
    const $from = ref.$from;
    const $to = ref.$to;

    const inList = $from.blockRange($to, function (node) {
        return node.childCount && node.firstChild.type.name === 'list_item';
    });

    if (inList) {
        return false;
    }

    const range = $from.blockRange($to), target = range && liftTarget(range);
    if (target == null) {
        return false;
    }
    if (dispatch) {
        dispatch(state.tr.lift(range, target).scrollIntoView());
    }
    return true;
}

// :: MenuItem
// Menu item for the `selectParentNode` command.
export const selectParentNodeItem = function () {
    return new MenuItem({
        title: "Select parent node",
        run: selectParentNode,
        select: state => selectParentNode(state),
        icon: icons.selectParentNode
    });
};

// :: (Object) → MenuItem
// Menu item for the `undo` command.
export let undoItem = function () {
    return new MenuItem({
        title: "Undo last change",
        run: undo,
        enable: state => undo(state),
        icon: icons.undo
    });
};

// :: (Object) → MenuItem
// Menu item for the `redo` command.
export let redoItem = function () {
    return new MenuItem({
        title: "Redo last undone change",
        run: redo,
        enable: state => redo(state),
        icon: icons.redo
    });
};

// :: (NodeType, Object) → MenuItem
// Build a menu item for wrapping the selection in a given node type.
// Adds `run` and `select` properties to the ones present in
// `options`. `options.attrs` may be an object or a function, as in
// `toggleMarkItem`.
export function wrapItem(nodeType, options) {
    let passedOptions = {
        run(state, dispatch) {
            // FIXME if (options.attrs instanceof Function) options.attrs(state, attrs => wrapIn(nodeType, attrs)(state))
            return wrapIn(nodeType, options.attrs)(state, dispatch)
        },
        select(state) {
            return wrapIn(nodeType, options.attrs instanceof Function ? null : options.attrs)(state)
        }
    };
    for (let prop in options) passedOptions[prop] = options[prop]
    return new MenuItem(passedOptions)
}

// :: (NodeType, Object) → MenuItem
// Build a menu item for changing the type of the textblock around the
// selection to the given type. Provides `run`, `active`, and `select`
// properties. Others must be given in `options`. `options.attrs` may
// be an object to provide the attributes for the textblock node.
export function blockTypeItem(nodeType, options) {
    let command = setBlockType(nodeType, options.attrs)
    let passedOptions = {
        run: command,
        enable(state) {
            return command(state)
        },
        active(state) {
            let {$from, to, node} = state.selection
            if (node) return node.hasMarkup(nodeType, options.attrs)
            return to <= $from.end() && $from.parent.hasMarkup(nodeType, options.attrs)
        }
    };
    for (let prop in options) passedOptions[prop] = options[prop]
    return new MenuItem(passedOptions)
}

export function canInsert(state, nodeType) {
    let $from = state.selection.$from;
    for (let d = $from.depth; d >= 0; d--) {
        let index = $from.index(d)
        if ($from.node(d).canReplaceWith(index, index, nodeType)) return true
    }
    return false
}

export function canInsertLink(state) {
    let allowLink = true;
    state.doc.nodesBetween(state.selection.$from.pos, state.selection.$to.pos, function (node) {
        if (node.type.spec.code) {
            allowLink = false;
        } else {
            node.marks.forEach(function (mark) {
                let spec = mark.type.spec;
                if (spec.preventMarks && $.inArray('link', spec.preventMarks) >= 0) {
                    allowLink = false;
                }
            });
        }
    });

    return allowLink;
}
