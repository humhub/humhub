/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import Database from 'emoji-picker-element/database';
import {getEmojiConfig} from '../../humhub-bridge';

/**
 * Unicode emoji version of the Noto font built by emoji.build.mjs
 */
const NOTO_EMOJI_VERSION = 18;

/**
 * The font stack of the picker while the Noto font is in use, see `$font-family-emoji` in variables.scss
 */
const NOTO_FONT_FAMILY = "'HumHub Emoji', 'Apple Color Emoji', 'Segoe UI Emoji', 'Noto Color Emoji', sans-serif";

let database = null;

/**
 * @returns {{dataSource: string, locale: string}} the picker data of the user's language
 */
export function getDataOptions() {
    const config = getEmojiConfig();

    return {
        dataSource: config.dataUrl || 'emoji/data/en.json',
        locale: config.locale || 'en',
    };
}

/**
 * Whether the emojis are rendered by the Noto font. Browsers without COLRv1 (Safari) skip the font and show their
 * own emojis.
 */
export function isNotoFont() {
    return !!getEmojiConfig().font
        && typeof CSS !== 'undefined' && typeof CSS.supports === 'function'
        && CSS.supports('font-tech(color-COLRv1)');
}

/**
 * Options of a picker element: the font and version of the Noto font, so the picker does not hide emojis the
 * operating system cannot render
 */
export function getPickerOptions() {
    const options = getDataOptions();

    if (isNotoFont()) {
        options.emojiVersion = NOTO_EMOJI_VERSION;
    }

    return options;
}

export function getPickerFontFamily() {
    return isNotoFont() ? NOTO_FONT_FAMILY : null;
}

/**
 * @returns {Database} the emoji database shared with the pickers (IndexedDB, one per locale)
 */
export function getDatabase() {
    const options = getDataOptions();

    if (!database || database.dataSource !== options.dataSource || database.locale !== options.locale) {
        database = new Database(options);
    }

    return database;
}

/**
 * The picker texts, translated by the editor
 */
export function getPickerI18n(translate) {
    const t = (key) => (translate ? translate(key) : key) || key;

    return {
        categoriesLabel: t('Categories'),
        emojiUnsupportedMessage: t('Your browser does not support color emoji.'),
        favoritesLabel: t('Frequently used'),
        loadingMessage: t('Loading...'),
        networkErrorMessage: t('Could not load emoji.'),
        regionLabel: t('Emoji picker'),
        searchDescription: t('When search results are available, press up or down to select and enter to choose.'),
        searchLabel: t('Search'),
        searchResultsLabel: t('Search results'),
        skinToneDescription: t('When expanded, press up or down to select and enter to choose.'),
        skinToneLabel: t('Choose a skin tone (currently {skinTone})'),
        skinTonesLabel: t('Skin tones'),
        skinTones: [
            t('Default'),
            t('Light'),
            t('Medium-Light'),
            t('Medium'),
            t('Medium-Dark'),
            t('Dark'),
        ],
        categories: {
            custom: t('Custom'),
            'smileys-emotion': t('Smileys & Emotion'),
            'people-body': t('People & Body'),
            'animals-nature': t('Animals & Nature'),
            'food-drink': t('Food & Drink'),
            'travel-places': t('Travel & Places'),
            activities: t('Activities'),
            objects: t('Objects'),
            symbols: t('Symbols'),
            flags: t('Flags'),
        },
    };
}

/**
 * The emoji with the user's preferred skin tone, if it has skin tones
 */
export function applySkinTone(emoji, skinTone) {
    if (skinTone && emoji.skins) {
        const skin = emoji.skins.find((s) => s.tone === skinTone);
        if (skin) {
            return skin.unicode;
        }
    }

    return emoji.unicode;
}
