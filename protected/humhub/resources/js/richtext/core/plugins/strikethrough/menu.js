/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

import {icons, markItem} from "../../menu/menu"


function markStrikethrough(context) {
    return markItem(context.schema.marks.strikethrough, {
        title: context.translate("Toggle strikethrough"),
        icon: icons.strikethrough,
        sortOrder: 300
    }, context);
}

export function menu(context) {
    return [
        {
            id: 'markStrikethrough',
            mark: 'strikethrough',
            group: 'marks',
            item: markStrikethrough(context)
        }
    ]
}