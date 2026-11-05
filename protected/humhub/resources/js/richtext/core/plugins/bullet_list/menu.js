/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

import {wrapInList} from "prosemirror-schema-list"
import {icons, cmdItem} from "../../menu"

function wrapBulletList(context) {
    return cmdItem(wrapInList(context.schema.nodes.bullet_list), {
        title: context.translate("Wrap in bullet list"),
        icon: icons.bulletList,
        hideOnCollapse: true,
        sortOrder: 100
    });
}

export function menu(context) {
    return [
        {
            id: 'wrapBulletList',
            node: 'bullet_list',
            group: 'format',
            item: wrapBulletList(context)
        }
    ]
}