/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import old_emojilib from "./emojilib-legacy";
import emoji_shortcuts from "markdown-it-emoji/lib/data/shortcuts";
import emojiNameMap from "emoji-name-map";
import twemoji from "twemoji";
import keywordSet from "emojilib";
import emojiData from "unicode-emoji-json";
import groupedEmojiData from "unicode-emoji-json/data-by-group";
import {getEmojiConfig} from "../../humhub-bridge";

// Fix shortcut ":)" from 😃 to 🙂
emoji_shortcuts.slightly_smiling_face = emoji_shortcuts.smiley;
delete emoji_shortcuts.smiley;

const emoji_defs = {};
const emoji_markdown_it_defs = {};
const emoji_defs_by_char = (function() {
    const result = {};

    $.each(emojiData, function (emoji, def) {
        $.each(old_emojilib, function (key, item) {
            if (emoji === item.char && key !== def.name) {
                def.char = emoji;
                def.keywords = item.keywords;
                emoji_defs[key] = def;
                emoji_markdown_it_defs[key] = emoji;
                return false;
            }
        });

        result[emoji] = def.name;
        emojiNameMap.emoji[def.name] = emoji;
        emoji_markdown_it_defs[def.name] = emoji;
    });

    return result;
})();

// Flatten shortcuts to simple object: { alias: emoji_name }
const shortcuts = Object.keys(emoji_shortcuts).reduce((acc, key) => {
    if (Array.isArray(emoji_shortcuts[key])) {
        emoji_shortcuts[key].forEach((alias) => {
            acc[alias] = key;
        });
        return acc;
    }

    acc[emoji_shortcuts[key]] = key;
    return acc;
}, {});

const getEmojiDefinitionByShortcut = (shortcut) => {
    const result = {
        name: getNameByShortcut(shortcut)
    };

    if (result.name) {
        result.emoji = getCharByName(result.name);
    }

    if (result.emoji) {
        result.$dom = getCharToDom(result.emoji)
    }

    return result;
};

const getNameByShortcut = (shortcut) => {
    return String(shortcuts[shortcut]);
};

const getCharByName = (name) => {
    return emojiNameMap.get(name);
};

const getNameByChar = (emojiChar) => {
    return String(emoji_defs_by_char[emojiChar]);
};

const getCharToDom = (emojiChar, name) => {
    name = (typeof name !== 'undefined') ? name : emoji_defs_by_char[emojiChar];
    name = String(name);

    const config = getEmojiConfig();
    const twemojiConfig = config.twemoji || {};
    twemojiConfig.attributes = (icon, variant) => {
        return {
            'data-name': name,
            'style': 'width:16px'
        };
    };

    const parsed = twemoji.parse(emojiChar, twemojiConfig);

    if (parsed && parsed.length) {
        try {
            return $(parsed).first();
        } catch (e) {
            console.error(e);
        }
        return '';
    }
};


const byCategory = {};

const getByCategory = (category) => {
    if (category === 'Search')
        return [];

    if (!byCategory[category]) {
        groupedEmojiData[category].forEach((emojiDef) => {
            emojiDef.keywords = keywordSet[emojiDef.emoji];
        });
        byCategory[category] = groupedEmojiData[category];
    }

    return byCategory[category];
};

const getMarkdownItOpts = () => {
    return {defs: emoji_markdown_it_defs};
};

export {
    shortcuts,
    twemoji,
    getNameByChar,
    getCharByName,
    getEmojiDefinitionByShortcut,
    getCharToDom,
    getMarkdownItOpts,
    getByCategory
};
