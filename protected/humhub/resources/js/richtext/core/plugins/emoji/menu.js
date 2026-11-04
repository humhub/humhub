/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {canInsert, canInsertLink, icons, MenuItem} from "../../menu/menu";
import {SimpleEmojiState} from "./state";
import {getProvider} from "./provider";

function insertEmoji(context) {
    return new MenuItem({
        title: context.translate("Insert Emoji"),
        icon: icons.emoji,
        sortOrder: 350,
        enable(state) {
            return canInsert(state, context.schema.nodes.image) && canInsertLink(state);
        },
        run(state, _, view, e) {
            if (!$('.humhub-richtext-provider:visible').length) {
                setTimeout(() => {
                    new SimpleEmojiState(getProvider(context)).update(state, view, e.target);
                }, 50);
            }
        }
    })
}

export function menu(context) {
    return [{
        id: 'insertEmoji',
        node: 'emoji',
        item: insertEmoji(context)
    }];
}
