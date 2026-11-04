/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import twemoji from "twemoji";
import {Plugin, PluginKey} from 'prosemirror-state';
import {EmojiQueryState} from './state';
import {getProvider} from "./provider";
import {getNameByChar} from "./util";

const pluginKey = new PluginKey('emoji');

const emojiPlugin = (context) => {
    return new Plugin({
        props: {
            transformPastedText: (text) => {
                text = twemoji.parse(text, context.getPluginOption('emoji', 'twemoji'));

                // eslint-disable-next-line
                return text.replace(/\<img class="emoji"[^\\\>]* alt=\"([^\"]*)\"[^\\\>]*\/>/g, function (match, char) {
                    return ':' + getNameByChar(char) + ':';
                });
            },
        },
        state: {
            init(config, state) {
                return new EmojiQueryState(state, {
                    provider: getProvider(context)
                });
            },
            apply(tr, prevPluginState, oldState, newState) {
                return prevPluginState;
            }
        },
        key: pluginKey,
        view: (view) => {
            const emojiState = pluginKey.getState(view.state);

            return {
                update(view, prevState) {
                    emojiState.update(view.state, view);
                },
                destroy() {}
            };
        },
    });
};

export {emojiPlugin, pluginKey};
