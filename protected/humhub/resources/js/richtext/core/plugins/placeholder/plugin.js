/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

import { Plugin } from 'prosemirror-state';
import { DecorationSet, Decoration } from 'prosemirror-view'
import {focusKey} from '../focus/plugin'

const placeholderPlugin = (context) => {
    return new Plugin({
        state: {
            init(config, state) {
                if(!isEmpty(state.doc, context)) {
                    return DecorationSet.empty;
                } else {
                    return DecorationSet.create(state.doc, [createDecoration(state.doc, context)]);
                }
            },
            apply(tr, set, state, newState) {
                // TODO: Currently if we leave the node with an empty e.g heading there is no placeholder
                // We should check when focusout, if the node is empty and change the first child to a paragraph

                if(focusKey.getState(newState)) {
                    return DecorationSet.empty;
                }

                if (!isEmpty(tr.doc, context)) {
                    return DecorationSet.empty;
                }

                return set.add(tr.doc, [createDecoration(tr.doc, context)]);
            }
        },
        props: {
            decorations(state) {
                return this.getState(state);
            }
        }
    });
};

const isEmpty = (doc, context) => {
    return doc.childCount === 1
        && doc.firstChild.type.name === 'paragraph'
        && doc.firstChild.content.size === 0 &&
        !context.hasContentDecorations()
};

let createDecoration = function(doc, context) {
    const node = document.createElement('div');
    node.textContent = context.options.placeholder.text;
    node.className = context.options.placeholder['class'] || 'placeholder';
    return Decoration.widget(1, node);
};

export {placeholderPlugin}