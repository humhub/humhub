/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {Plugin, PluginKey} from 'prosemirror-state';

const focusKey = new PluginKey('focus');

const focusPlugin = (context) => {
    return new Plugin({
        key: focusKey,
        state: {
            init() {
                return false;
            },
            apply(transaction, prevFocused) {
                let focused = transaction.getMeta(focusKey);
                if (typeof focused === 'boolean') {
                    return focused;
                }
                return prevFocused;
            }
        },
        props: {
            handleDOMEvents: {
                blur: view => {
                    view.dispatch(view.state.tr.setMeta(focusKey, false));
                    return false;
                },
                focus: (view, event) => {
                    view.dispatch(view.state.tr.setMeta(focusKey, true));
                    return false;
                }
            }
        }
    });
};

export {focusPlugin, focusKey};
