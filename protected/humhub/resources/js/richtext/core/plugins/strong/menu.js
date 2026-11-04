/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

import {icons, markItem, wrapSourceTextMark} from "../../menu"


function markStrong(context) {
    return markItem(context.schema.marks.strong, {
        title: context.translate("Toggle strong style"),
        icon: icons.strong,
        sortOrder: 100
    }, context);
}

export function menu(context) {
    return [
        {
            id: 'markStrong',
            mark: 'strong',
            group: 'marks',
            item: markStrong(context)
        }
    ]
}