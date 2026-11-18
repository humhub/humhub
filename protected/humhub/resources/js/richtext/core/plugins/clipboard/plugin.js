/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {Plugin, Selection} from 'prosemirror-state';
import {Node, Slice} from 'prosemirror-model';
import {getParser} from "../../../markdown";
import {triggerUpload, canUpload} from "../upload/service";
import {$node} from "../../util/node";

const clipboardPlugin = (context) => {
    let parser = getParser(context);

    return new Plugin({
        props: {
            clipboardTextParser: $.proxy(parser.parse, parser),
            transformPasted: (slice) => {
                if (slice && slice instanceof Node && slice.type === context.schema.nodes.doc) {
                    return new Slice(slice.content, 0, 0)
                } else {
                    try {
                        let selectionMarks = getSelectionMarks(context);

                        if (selectionMarks && selectionMarks.length) {
                            applyMarksToRawText(slice, selectionMarks)
                        }
                    } catch (e) {
                        console.warn(e);
                    }
                }

                return slice;
            },
            handleDOMEvents: {
                paste: (view, e) => {
                    if (e.clipboardData.files && e.clipboardData.files.length) {
                        triggerUpload(view.state, view, context, e.clipboardData.files);
                        e.preventDefault();
                    }
                },
                drop: (view, e) => {
                    const files = e.dataTransfer && e.dataTransfer.files;
                    if (!files || !files.length || !canUpload(context)) {
                        return false;
                    }

                    // Insert the dropped files into the text at the drop position, as pasted ones, instead of
                    // letting the upload widget of the form attach them under the form.
                    selectDropPosition(view, e);
                    triggerUpload(view.state, view, context, files);
                    e.preventDefault();
                    e.stopPropagation();

                    return true;
                }
            }
        },
    });
};

function selectDropPosition(view, e) {
    try {
        const coords = view.posAtCoords({left: e.clientX, top: e.clientY});
        if (coords) {
            view.dispatch(view.state.tr.setSelection(Selection.near(view.state.doc.resolve(coords.pos))));
        }
    } catch (err) {
        console.warn(err);
    }
}

function getSelectionMarks(context) {
    if (context.editor.view.state.storedMarks && context.editor.view.state.storedMarks.length) {
        return context.editor.view.state.storedMarks
    }

    let selection = context.editor.view.state.selection;
    let nodeBefore = selection.$from.nodeBefore;
    return nodeBefore ? nodeBefore.marks : null;
}

function applyMarksToRawText(slice, marks) {
    let fragment = slice.content;
    let firstChild = fragment.firstChild;

    if (!firstChild) {
        return;
    }

    let texts = $node(firstChild).find('text');
    if (texts.flat.length) {
        let firstText = texts.flat[0];
        if (firstText.isPlain()) {
            firstText.addMarks(marks);
        }
    }
}

export {clipboardPlugin};
