/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {Plugin} from 'prosemirror-state';
import {Node, Slice} from 'prosemirror-model';
import {getParser} from "../../../markdown";
import {triggerUpload} from "../upload/service";
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
                }
            }
        },
    });
};

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
