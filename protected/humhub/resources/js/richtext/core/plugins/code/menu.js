/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

import {icons, markItem} from "../../menu/menu"


function markCode(context) {
    return markItem(context.schema.marks.code, {
        title: context.translate("Toggle code font"),
        icon: icons.code,
        sortOrder: 400
    }, context);
}

export function menu(context) {
    return [
        {
            id: 'markCode',
            mark: 'code',
            group: 'marks',
            item: markCode(context)
        }
    ]
}