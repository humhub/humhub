/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {icons, wrapItem} from "../../menu/menu";

function wrapBlockQuote(context) {
    return wrapItem(context.schema.nodes.blockquote, {
        title: context.translate("Wrap in block quote"),
        icon: icons.blockquote,
        hideOnCollapse: true,
        sortOrder: 300
    });
}

export function menu(context) {
    return [{
        id: 'wrapBlockQuote',
        node: 'blockquote',
        group: 'format',
        item: wrapBlockQuote(context)
    }];
}
