/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {liftListItem, sinkListItem} from "prosemirror-schema-list";
import {addRowAfter, goToNextCell} from "prosemirror-tables";

let keymap = () => {
    const getNextSelectionCell = ($from) => {
        let depth = $from.depth;
        let parent;

        do {
            parent = $from.node(depth);
            if (parent) {
                if (parent.type.name === "table_header" || parent.type.name === "table_cell") {
                    const table = $from.node(depth - 2); // Get the table node
                    const row = $from.node(depth - 1);
                    // Check if the current cell is the last cell in the table
                    const isLastCell = $from.index(depth - 2) === (table.childCount - 1)
                        && $from.index(depth - 1) === (row.childCount - 1);

                    return !isLastCell;
                }
                depth--;
            }
        } while (depth > 0 && parent);

        return null;
    };

    const getLineStartPos = ($from) => {
        const content = $from.node($from.depth).content.content[0];
        const text = content ? content.text : '';
        const startPos = $from.start() - 1;
        let lineStart = $from.pos;

        if ($from.pos === $from.end() && text.charAt(lineStart - 2) === '\n') {
            lineStart--;
        } else if ($from.pos !== $from.start()) {
            while (lineStart === $from.pos || (lineStart > startPos && text.charAt(lineStart - startPos - 1) !== '\n')) {
                lineStart--;
            }
        }

        return lineStart >= $from.pos ? startPos : lineStart;
    }

    return {
        'Tab': (state, dispatch, view) => {
            if (dispatch) {
                const {$from} = state.selection;
                const parent = $from.node($from.depth - 1);
                const nodeType = parent ? parent.type : null;

                if (state.selection.empty && $from.depth > 1 && nodeType.name === "list_item") {
                    sinkListItem(nodeType)(state, dispatch);
                } else {
                    const nextSelection = getNextSelectionCell($from);
                    if (nextSelection !== null) {
                        if (nextSelection) {
                            goToNextCell(1)(state, dispatch);
                        } else {
                            addRowAfter(state, dispatch);
                            view.dispatchEvent(new KeyboardEvent('keydown', {key: 'Tab', code: 9}));
                        }
                    } else {
                        return false;
                    }
                }
            }
            return true;
        },
        "Shift-Tab": (state, dispatch) => {
            if (dispatch) {
                const {$from} = state.selection;
                const parent = $from.node($from.depth - 1);
                const nodeType = parent ? parent.type : null;

                if (state.selection.empty && $from.depth > 1 && nodeType.name === "list_item") {
                    liftListItem(nodeType)(state, dispatch);
                } else {
                    const nextSelection = getNextSelectionCell($from);
                    if (nextSelection !== null) {
                        goToNextCell(-1)(state, dispatch);
                    } else {
                        return false;
                    }
                }
            }
            return true;
        }
    };
};

export {keymap};
