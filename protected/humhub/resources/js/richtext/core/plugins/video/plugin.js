/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {NodeSelection, Plugin} from "prosemirror-state";
import {editNode} from "./menu";
import {validateHref} from "../../util/linkUtil";
import {getClassForFloat} from "../image/imageFloat";

const videoPlugin = (context) => {
    context.editor.$.on('mouseleave', (e) => {
        let target = e.toElement || e.relatedTarget;
        if (!$(target).closest('.humhub-richtext-inline-menu').length) {
            $('.humhub-richtext-inline-menu').remove();
        }
    });

    return new Plugin({
        props: {
            nodeViews: {
                video(node) {
                    return new VideoView(node, context);
                }
            }
        },
        filterTransaction: (tr) => {
            if (!(tr.curSelection instanceof NodeSelection)) {
                $('.humhub-richtext-video-edit').remove();
            }

            return true;
        }
    });
};

class VideoView {
    constructor(node, context) {
        this.createDom(node);

        context.event.on('clear, serialize', function () {
            $('.humhub-richtext-inline-menu').remove();
        });

        this.dom.addEventListener("mouseenter", () => {
            const $video = $(this.dom);
            const offset = $video.offset();
            const editorOffset = context.editor.$.offset();

            if (offset.top < editorOffset.top) {
                return;
            }

            const $edit = $('<div>').addClass('humhub-richtext-inline-menu').addClass('humhub-richtext-video-edit')
                .html('<button class="btn btn-primary btn-sm btn-icon-only"><i class="fa fa-pencil"></i></button>')
                .css({
                    position: 'absolute',
                    left: offset.left + $video.width() - 25,
                    top: offset.top + 5,
                    'z-index': 9999
                }).on('mousedown', () => {
                    const view = context.editor.view;
                    const doc = view.state.doc;
                    view.dispatch(view.state.tr.setSelection(NodeSelection.create(doc, view.posAtDOM(this.dom))).scrollIntoView());
                    editNode(node, context, view);
                });

            $('html').append($edit);
        });

        this.dom.addEventListener("mouseleave", (e) => {
            let target = e.toElement || e.relatedTarget;
            if (!$(target).closest('.humhub-richtext-inline-menu').length) {
                $('.humhub-richtext-inline-menu').remove();
            }
        });
    }

    createDom(node) {
        const src = validateHref(node.attrs.src) ? node.attrs.src : '#';
        this.dom = $('<video>').attr({
            src: src,
            title: node.attrs.title || null,
            width: node.attrs.width || null,
            height: node.attrs.height || null,
            class: getClassForFloat(node.attrs.float),
            'data-file-guid': node.attrs.fileGuid || null
        })[0];

        ['controls', 'autoplay', 'muted', 'loop'].forEach((attr) => {
            if (node.attrs[attr]) {
                this.dom.setAttribute(attr, attr);
            }
        });
    }
}

export {videoPlugin};
