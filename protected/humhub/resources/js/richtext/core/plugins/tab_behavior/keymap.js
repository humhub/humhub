/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {liftListItem, sinkListItem} from "prosemirror-schema-list";
import {goToNextCell} from "prosemirror-tables";
import {Selection, TextSelection} from "prosemirror-state";

let keymap = () => {
    // The type of the list item the cursor is in, else null
    const getListItemType = (state) => {
        const {$from} = state.selection;
        const parent = $from.depth > 1 ? $from.node($from.depth - 1) : null;
        return state.selection.empty && parent && parent.type.name === "list_item" ? parent.type : null;
    };

    // The depth of the table around the cursor, else null
    const getTableDepth = ($from) => {
        for (let depth = $from.depth; depth > 0; depth--) {
            if ($from.node(depth).type.name === "table") {
                return depth;
            }
        }
        return null;
    };

    // Puts the cursor after (dir 1) or before (dir -1) the table, into a new paragraph when there is
    // nothing to put it into, so Tab never traps the user in a table (docs/ui/keyboard.md, "The editor")
    const leaveTable = (state, dispatch, tableDepth, dir) => {
        if (!dispatch) {
            return true;
        }
        const {$from} = state.selection;
        const pos = dir > 0 ? $from.after(tableDepth) : $from.before(tableDepth);
        let tr = state.tr;
        const $pos = tr.doc.resolve(pos);
        // Only into a text block right next to the table; otherwise (another table, a code block, the end
        // of the document or of the list item) a paragraph is inserted, so the cursor can always land there
        const neighbour = dir > 0 ? $pos.nodeAfter : $pos.nodeBefore;
        let selection = neighbour && neighbour.isTextblock ? Selection.findFrom($pos, dir, true) : null;
        if (!selection) {
            tr = tr.insert(pos, state.schema.nodes.paragraph.create());
            selection = TextSelection.create(tr.doc, pos + 1);
        }
        dispatch(tr.setSelection(selection).scrollIntoView());
        return true;
    };

    const tab = (dir) => (state, dispatch) => {
        const listItemType = getListItemType(state);
        if (listItemType) {
            (dir > 0 ? sinkListItem : liftListItem)(listItemType)(state, dispatch);
            return true;
        }

        const tableDepth = getTableDepth(state.selection.$from);
        if (tableDepth === null) {
            return false; // Not ours: Tab leaves the editor
        }

        return goToNextCell(dir)(state, dispatch) || leaveTable(state, dispatch, tableDepth, dir);
    };

    return {
        'Tab': tab(1),
        'Shift-Tab': tab(-1),
    };
};

export {keymap};
