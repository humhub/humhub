import {Plugin, PluginKey} from "prosemirror-state";

export const toggleNavPluginKey = new PluginKey('toggleNav');

export function isCollapsed(state) {
    return toggleNavPluginKey.getState(state);
}

export function toggleNavPlugin(context) {
    return new Plugin({
        key: toggleNavPluginKey,
        state: {
            init(config, state) {
                return localStorage.getItem("isExpandedToolbar") !== 'true';
            },
            apply(tr, prevPluginState, oldState, newState) {
                let meta = tr.getMeta(toggleNavPluginKey);
                if (typeof meta !== 'undefined') {
                    localStorage.setItem("isExpandedToolbar", !meta);
                }

                return typeof meta !== 'undefined' ? meta : prevPluginState;
            }
        },
    })
}
