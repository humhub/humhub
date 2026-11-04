/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {NodeSelection, Plugin} from 'prosemirror-state';

import {editNode} from './menu';
import {getClassForFloat} from './imageFloat';
import {validateHref} from "../../util/linkUtil";

const imagePlugin = (context) => {
    context.editor.$.on('mouseleave', e => {
        let target = e.toElement || e.relatedTarget;
        if (!$(target).closest('.humhub-richtext-inline-menu').length) {
            $('.humhub-richtext-inline-menu').remove();
        }
    });

    return new Plugin({
        props: {
            nodeViews: {
                image(node) {
                    return new ImageView(node, context);
                }
            },
        },
        filterTransaction: (tr, state) => {
            if (!(tr.curSelection instanceof NodeSelection)) {
                $('.humhub-richtext-image-edit').remove();
            }

            return true;
        }
    });
};

class ImageView {
    constructor(node, context) {
        // The editor will use this as the node's DOM representation
        this.createDom(node);

        context.event.on('clear, serialize', function () {
            $('.humhub-richtext-inline-menu').remove();
        });

        this.dom.addEventListener("mouseenter", e => {
            let $img = $(this.dom);
            let offset = $img.offset();
            let editorOffset = context.editor.$.offset();

            if (offset.top < editorOffset.top) {
                return;
            }

            let $edit = $('<div>').addClass('humhub-richtext-inline-menu').addClass('humhub-richtext-image-edit')
                .html('<button class="btn btn-primary btn-sm btn-icon-only"><i class="fa fa-pencil"></i></button>')
                .css({
                    position: 'absolute',
                    left: offset.left + $img.width() - (25),
                    top: offset.top + 5,
                    'z-index': 9999
                }).on('mousedown', (evt) => {
                    let view = context.editor.view;
                    let doc = view.state.doc;
                    view.dispatch(view.state.tr.setSelection(NodeSelection.create(doc, view.posAtDOM(this.dom))).scrollIntoView());
                    editNode(node, context, view);
                });

            $('html').append($edit);
        });

        this.dom.addEventListener("mouseleave", e => {
            let target = e.toElement || e.relatedTarget;
            if (!$(target).closest('.humhub-richtext-inline-menu').length) {
                $('.humhub-richtext-inline-menu').remove();
            }
        });
    }

    createDom(node) {
        let src = validateHref(node.attrs.src) ? node.attrs.src : '#';

        this.dom = $('<img>').attr({
            src: src,
            title: node.attrs.title || null,
            width: node.attrs.width || null,
            height: node.attrs.height || null,
            alt: node.attrs.alt || null,
            class: getClassForFloat(node.attrs.float),
            'data-file-guid': node.attrs.fileGuid
        })[0];
    }

    //stopEvent() { return true }
}

export {imagePlugin};
