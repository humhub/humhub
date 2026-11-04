/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

import {Plugin} from "prosemirror-state"
import {Decoration, DecorationSet} from "prosemirror-view"
import {getLoaderWidget} from "../../humhub-bridge";


const loaderPlugin = (context) => {
    return new Plugin({
        state: {
            init() {
                return DecorationSet.empty
            },
            apply(tr, set) {
                // Adjust decoration positions to changes made by the transaction
                set = set.map(tr.mapping, tr.doc);
                // See if the transaction adds or removes any placeholders
                let action = tr.getMeta(this);
                if (action && action.add) {
                    let deco = Decoration.widget(action.add.pos, getLoaderWidget()[0], {id: action.add.id, content: true});
                    set = set.add(tr.doc, [deco]);
                    context.addContentDecoration('loader');
                } else if (action && action.remove) {
                    set = set.remove(set.find(null, null, spec => spec.id === action.remove.id));
                    context.removeContentDecoration('loader');
                }
                return set
            }
        },
        props: {
            decorations(state) {
                return this.getState(state)
            }
        }
    });
};

function findLoader(context, id) {
    let decos = context.getProsemirrorPlugins('loader')[0].getState(context.editor.view.state);
    let found = decos.find(null, null, spec => spec.id === id);
    return found.length ? found[0].from : null
}

function loaderStart(context, id, dispatch) {
    let view = context.editor.view;
    let tr = view.state.tr;

    if (!tr.selection.empty) {
        tr.deleteSelection();
    }

    tr.setMeta(context.getProsemirrorPlugins('loader')[0], {add: {id, pos: tr.selection.from}});

    if(dispatch) {
        view.dispatch(tr);
    }

    return tr;
}

function replaceLoader(context, id, content, dispatch) {
    let view = context.editor.view;
    let pos = findLoader(context, id);

    // If the content around the placeholder has been deleted, drop the image
    if (pos === null) {
        return;
    }

    let tr = view.state.tr.replaceWith(pos, pos, content).setMeta(context.getProsemirrorPlugins('loader')[0], {remove: {id}});

    if(dispatch) {
        view.dispatch(tr);
    }

    return tr;
}

function removeLoader(context, id, dispatch) {
    let view = context.editor.view;
    let pos = findLoader(context, id);

    // Focus the editor in order to synchronized changes into hidden textarea
    // for case when before file uploading the editor was not focused
    view.focus();

    // If the content around the placeholder has been deleted, drop the image
    if (pos === null) {
        return;
    }

    let tr = view.state.tr.setMeta(context.getProsemirrorPlugins('loader')[0], {remove: {id}});

    if(dispatch) {
        view.dispatch(tr);
    }

    return tr;
}

export {loaderPlugin, loaderStart,  replaceLoader, removeLoader}