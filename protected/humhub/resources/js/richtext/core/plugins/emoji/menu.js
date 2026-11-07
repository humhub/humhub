/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {canInsertLink, icons, MenuItem} from "../../menu/menu";
import {openEmojiPicker} from "./picker";

function insertEmoji(context) {
    return new MenuItem({
        title: context.translate("Insert Emoji"),
        icon: icons.emoji,
        sortOrder: 350,
        enable(state) {
            // Not in code, where the font and the emoji would not fit
            return canInsertLink(state);
        },
        run(state, _, view, e) {
            const anchor = (e && e.target && e.target.closest('button, .ProseMirror-menuitem')) || view.dom;

            // After the mousedown, whose outside-click handler (richtext/index.js) would close the picker again
            setTimeout(() => openEmojiPicker(anchor, {
                translate: (key) => context.translate(key),
                onSelect: (emoji) => {
                    view.dispatch(view.state.tr.insertText(emoji).scrollIntoView());
                    view.focus();
                },
                onClose: (restoreFocus) => {
                    if (restoreFocus) {
                        view.focus();
                    }
                },
            }));
        }
    });
}

export function menu(context) {
    return [{
        id: 'insertEmoji',
        item: insertEmoji(context)
    }];
}
