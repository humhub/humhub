/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

import {icons, markItem} from "../../menu/menu"


function markEm(context) {
    return markItem(context.schema.marks.em, {
        title: context.translate("Toggle emphasis"),
        icon: icons.em,
        sortOrder: 200
    }, context);
}

export function menu(context) {
    return [
        {
            id: 'markEm',
            mark: 'em',
            group: 'marks',
            item: markEm(context)
        }
    ]
}