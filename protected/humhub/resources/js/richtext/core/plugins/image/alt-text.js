/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {validateHref} from "../../util/linkUtil";
import {mountVueComponent} from "../../humhub-bridge";

// The platform's dialog (protected/humhub/vue/ImageAltTextModal.vue)
const MODAL_COMPONENT = 'ImageAltTextModal';

// Images waiting for their alt text, per editor: one dialog at a time
const queues = new WeakMap();

/**
 * Asks for the alt text of each image among the inserted nodes, one after another, in the
 * platform's dialog (none without the Vue runtime, e.g. outside HumHub). Skipping keeps the alt
 * text the image was inserted with (its file name). Disabled with `image: {altTextPrompt: false}`.
 */
const requestAltText = function (context, nodes) {
    const imageType = context.schema.nodes.image;

    if (!imageType || context.getPluginOption('image', 'altTextPrompt', true) === false) {
        return;
    }

    const images = (nodes || []).filter((node) => node && node.type === imageType);

    if (!images.length) {
        return;
    }

    let queue = queues.get(context);
    const isOpen = !!queue;

    if (!queue) {
        queue = [];
        queues.set(context, queue);
    }

    queue.push(...images);

    if (!isOpen) {
        next(context);
    }
};

// Whether a dialog is open, or about to open, for this editor
const isAltTextPending = function (context) {
    return queues.has(context);
};

const next = function (context) {
    const queue = queues.get(context);
    const node = queue && queue.shift();

    if (!node) {
        queues.delete(context);
        return;
    }

    // An image removed in the meantime needs no alt text
    if (findPos(context, node) === null) {
        next(context);
        return;
    }

    const stop = () => queues.delete(context);

    const host = mountVueComponent(MODAL_COMPONENT, {src: validateHref(node.attrs.src) ? node.attrs.src : null}, stop);

    if (!host) {
        stop();
        return;
    }

    const close = (alt) => {
        host.remove();
        if (typeof alt === 'string') {
            setAlt(context, node, alt);
        }
        context.editor.view.focus();
        next(context);
    };

    host.addEventListener('altText:save', (evt) => close(evt.detail.alt));
    host.addEventListener('altText:cancel', () => close());
};

const setAlt = function (context, node, alt) {
    const view = context.editor.view;
    const pos = findPos(context, node);

    if (pos !== null) {
        view.dispatch(view.state.tr.setNodeMarkup(pos, null, Object.assign({}, node.attrs, {alt})));
    }
};

// The inserted node itself: a node is immutable, so it stays the same object until it is changed
const findPos = function (context, node) {
    let found = null;

    context.editor.view.state.doc.descendants((child, pos) => {
        if (found !== null) {
            return false;
        }
        if (child === node) {
            found = pos;
        }
    });

    return found;
};

export {requestAltText, isAltTextPending};
