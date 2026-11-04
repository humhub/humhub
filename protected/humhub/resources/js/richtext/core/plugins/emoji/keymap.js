/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {pluginKey} from './plugin';

let keymap = () => {
    return {
        'ArrowLeft': (state, dispatch) => {
            let emojiState = pluginKey.getState(state);

            if (emojiState && emojiState.active) {
                emojiState.provider.prev();
                return true;
            }

            return false;
        },
        'ArrowDown': (state, dispatch) => {
            let emojiState = pluginKey.getState(state);

            if (emojiState && emojiState.active) {
                emojiState.provider.down();
                return true;
            }

            return false;
        },
        'ArrowRight': (state, dispatch) => {
            let emojiState = pluginKey.getState(state);

            if (emojiState && emojiState.active) {
                emojiState.provider.next();
                return true;
            }

            return false;
        },
        'ArrowUp': (state, dispatch) => {
            let emojiState = pluginKey.getState(state);

            if (emojiState && emojiState.active) {
                emojiState.provider.up();
                return true;
            }

            return false;
        },
        'Tab': (state, dispatch) => {
            let emojiState = pluginKey.getState(state);

            if (emojiState && emojiState.active) {
                emojiState.provider.getChooser().nextCategory();
                return true;
            }

            return false;
        },
        'Enter': (state, dispatch) => {
            let emojiState = pluginKey.getState(state);

            if (emojiState && emojiState.active) {
                emojiState.provider.select();
                return true;
            }

            return false;
        },
        'Escape': (state, dispatch) => {
            let emojiState = pluginKey.getState(state);

            if (emojiState && emojiState.active) {
                emojiState.provider.reset();
                return true;
            }

            return false;
        }
    };
};

export {keymap};
