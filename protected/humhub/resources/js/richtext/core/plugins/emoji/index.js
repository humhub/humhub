/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {Plugin} from 'prosemirror-state';
import {autocompleteKeymap, autocompletePlugin} from './autocomplete';
import {shortcutRules} from './shortcuts';
import {menu} from './menu';

/**
 * Emojis are plain Unicode text. The plugin adds the picker (menu), the inline autocomplete (`:smi`) and the text
 * shortcuts (`:)`), and turns emoji images of pasted HTML into their text.
 */
const emoji = {
    id: 'emoji',
    menu: (context) => menu(context),
    inputRules: () => shortcutRules(),
    keymap: () => autocompleteKeymap(),
    plugins: () => [
        autocompletePlugin(),
        new Plugin({
            props: {
                // E.g. `<img class="emoji" alt="😀">` copied from HumHub < 1.20 or other sites
                transformPastedHTML: (html) => html.replace(
                    /<img\b[^>]*\bclass="[^"]*\bemoji\b[^"]*"[^>]*>/gi,
                    (img) => {
                        const alt = img.match(/\balt="([^"]*)"/i);
                        return alt ? alt[1] : img;
                    }
                ),
            },
        }),
    ],
};

export default emoji;
