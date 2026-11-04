import { Plugin, PluginKey } from 'prosemirror-state';
import { MentionState } from './state';
import {$node} from "../../util/node"

const pluginKey = new PluginKey('mention');

const mentionPlugin = (context) => {
    return new Plugin({
        state: {
            init(config, state) {
                return new MentionState(state, context.options.mention);
            },
            apply(tr, prevPluginState, oldState, newState) {
                return prevPluginState;
            }
        },
        key: pluginKey,
        view: (view) => {
            const mentionState = pluginKey.getState(view.state);

            return {
                update(view, prevState) {
                    mentionState.update(view.state, view);
                },
                destroy() {}
            };
        },
        appendTransaction: (transactions, oldState, newState) => {
            return $node(newState.doc).find('mention').mark('link').removeMark('link', newState);
        }
    });
};

export {mentionPlugin, pluginKey}