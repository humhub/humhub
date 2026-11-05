/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {MenuItem, Dropdown, icons} from "../../menu";
import {wrapIn} from "prosemirror-commands";
import {TextField, openPrompt} from "../../prompt";
import {
    addColumnAfter,
    addColumnBefore,
    deleteColumn,
    addRowAfter,
    addRowBefore,
    deleteRow,
    deleteTable,
    toggleHeaderRow
} from "prosemirror-tables";

function wrapTableItem(context) {
    let schema = context.schema;
    let command = wrapIn(schema.nodes.table_header);
    let itemOptions = {
        title: context.translate("Create table"),
        icon: icons.table,
        sortOrder: 300,
        hideOnCollapse: true,
        run(state, dispatch, view) {
            openPrompt({
                title: context.translate("Insert table"),
                fields: {
                    rowCount: new TextField({label: context.translate("Rows"), required: true, value: 1}),
                    columnCount: new TextField({label: context.translate("Columns"), value: 1})
                },
                callback(attrs) {
                    wrapIn(schema.nodes.table_header)(view.state, dispatch);

                    for (let i = 1; i < attrs.columnCount; i++) {
                        addColumnAfter(view.state, dispatch);
                    }

                    toggleHeaderRow(view.state, dispatch);
                    toggleHeaderRow(view.state, dispatch);

                    for (let i = 1; i < attrs.rowCount; i++) {
                        addRowAfter(view.state, dispatch);
                        //toggleHeaderRow();
                    }

                    view.focus();
                }
            })
        },
        enable(state) {
            return command(state);
        },
        select(state) {
            return command(state);
        }
    };

    return new MenuItem(itemOptions);
}

export function menu(context) {
    return [
        {
            id: 'insertTable',
            node: 'table',
            item: wrapTableItem(context)
        },
        {
            id: 'tableOptions',
            node: 'table',
            item: new Dropdown(buildTableMenu(context), {
                icon: icons.table,
                sortOrder: 301
            })
        }
    ];
}

let buildTableMenu = function (context) {
    function item(label, cmd, sortOrder) {
        return new MenuItem({label, select: cmd, run: cmd, sortOrder: sortOrder, title: ''});
    }

    return [
        item(context.translate("Insert column before"), addColumnBefore, 0),
        item(context.translate("Insert column after"), addColumnAfter, 1),
        item(context.translate("Delete column"), deleteColumn, 2),
        item(context.translate("Insert row before"), addRowBefore, 3),
        item(context.translate("Insert row after"), addRowAfter, 4),
        item(context.translate("Delete row"), deleteRow, 5),
        item(context.translate("Delete table"), deleteTable, 6)
    ];
};
