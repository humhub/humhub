/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

import {redoItem, undoItem} from "../../menu";

export function menu(context) {
    return [
        {
            id: 'undo',
            group: 'helper',
            item: undoItem()
        },
        {
            id: 'redo',
            group: 'helper',
            item: redoItem()
        },
    ]
}