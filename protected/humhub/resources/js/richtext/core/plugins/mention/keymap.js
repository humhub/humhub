import {pluginKey} from './plugin';

let keymap = () => {
    return {
        'ArrowUp': (state, dispatch) => {
            let mentionState = pluginKey.getState(state);

            if (mentionState && mentionState.active) {
                mentionState.provider.prev();
                return true;
            }

            return false;
        },
        'ArrowDown': (state, dispatch) => {
            let mentionState = pluginKey.getState(state);

            if (mentionState && mentionState.active) {
                mentionState.provider.next();
                return true;
            }

            return false;
        },
        'Enter': (state, dispatch) => {
            let mentionState = pluginKey.getState(state);

            if (mentionState && mentionState.active) {
                mentionState.provider.select();
                return true;
            }

            return false;
        },
        'Escape': (state, dispatch) => {
            let mentionState = pluginKey.getState(state);

            if (mentionState && mentionState.active) {
                mentionState.provider.reset();
                return true;
            }

            return false;
        }
    };
};

export {keymap};
