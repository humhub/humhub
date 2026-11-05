/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {blockTypeItem} from "../../menu/menu";

function makeParagraph(context) {
    return blockTypeItem(context.schema.nodes.paragraph, {
        title: context.translate("Change to paragraph"),
        label: context.translate("Paragraph")
    });
}

export function menu(context) {
    return [{
        id: 'makeParagraph',
        node: 'paragraph',
        group: 'types',
        sortOrder: 100,
        item: makeParagraph(context)
    }];
}
