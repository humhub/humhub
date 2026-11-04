/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {liftListItem, sinkListItem} from "prosemirror-schema-list";
import {icons, cmdItem} from "../../menu/menu";

function outdentListItem(context) {
    return cmdItem(liftListItem(context.schema.nodes.list_item), {
        title: context.translate("Decrease indent"),
        feature: true,
        icon: icons.outdent,
        sortOrder: 120
    });
}

function indentListItem(context) {
    return cmdItem(sinkListItem(context.schema.nodes.list_item), {
        title: context.translate("Increase indent"),
        icon: icons.indent,
        sortOrder: 130
    });
}

export function menu(context) {
    return [
        {
            id: 'outdentListItem',
            node: 'list_item',
            group: 'format',
            item: outdentListItem(context)
        },
        {
            id: 'indentListItem',
            node: 'list_item',
            group: 'format',
            item: indentListItem(context)
        }
    ];
}
