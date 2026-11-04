/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {blockTypeItem} from "../../menu/menu";

function makeHeading(context, i) {
    return blockTypeItem(context.schema.nodes.heading, {
        id: 'makeHeading' + i,
        attrs: {level: i},
        label: `${context.translate('Heading')} ${i}`,
        title: `${context.translate("Change to heading")} ${i} (${Array(i + 1).join("#")})`,
    });
}

export function menu(context) {
    const headers = [];

    for (let i = 1; i <= 4; i++) {
        headers.push({
            node: 'heading',
            group: 'types',
            sortOrder: 100 + i,
            item: makeHeading(context, i)
        });
    }

    return headers;
}
