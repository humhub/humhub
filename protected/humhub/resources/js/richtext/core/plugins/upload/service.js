/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

import {loaderStart, replaceLoader, removeLoader} from "../loader/plugin";

const isVideoFile = (file) => {
    return !!((file.mimeIcon && file.mimeIcon.indexOf('mime-video') === 0) ||
        (file.mimeType && file.mimeType.indexOf('video/') === 0) ||
        (file.type && file.type.indexOf('video/') === 0));
};

const isAudioFile = (file) => {
    return !!((file.mimeIcon && file.mimeIcon.indexOf('mime-audio') === 0) ||
        (file.mimeType && file.mimeType.indexOf('audio/') === 0) ||
        (file.type && file.type.indexOf('audio/') === 0));
};

const createNodeFromFile = function (context, file) {
    if (file.error) {
        return null;
    }

    // `fileName` is the HTTP API's file shape (`POST /api/v2/file`), `name` the legacy upload's
    file = Object.assign({}, file, {name: file.name || file.fileName});

    const schema = context.schema;

    if (file.mimeIcon === 'mime-image') {
        return schema.nodes.image.create({src: file.url, title: file.name, alt: file.name, fileGuid: file.guid});
    }

    if (schema.nodes.video && isVideoFile(file)) {
        return schema.nodes.video.create({
            src: file.url,
            title: file.name,
            controls: true,
            fileGuid: file.guid
        });
    }

    if (schema.nodes.audio && isAudioFile(file)) {
        return schema.nodes.audio.create({
            src: file.url,
            title: file.name,
            controls: true,
            fileGuid: file.guid
        });
    }

    const linkMark = schema.marks.link.create({href: file.url, fileGuid: file.guid});
    return schema.text(file.name, [linkMark]);
};

const triggerUpload = function(state, view, context, files) {
    // A fresh object to act as the ID for this upload
    let id = {};

    if (typeof context.options.upload === 'function') {
        uploadThroughOption(context, id, files);
        return;
    }

    let uploadWidget = humhub.require('ui.widget.Widget').instance($('#'+context.id+'-file-upload'));

    if (uploadWidget) {
        uploadWidget.off('uploadStart.richtext').on('uploadStart.richtext', (evt, response) => {
            // Replace the selection with a placeholder
            loaderStart(context, id, true);
        }).off('uploadEnd.richtext').on('uploadEnd.richtext', (evt, response) => {
            replaceLoader(context, id, createNodesFromResponse(context, response), true);
            console.log('UPLOADER uploadEnd.richtext')
        }).off('uploadFinish.richtext').on('uploadFinish.richtext', () => {
            // Make sure our loader is removed after upload
            removeLoader(context, id, true);
        });

        if (files) {
            uploadWidget.$.fileupload('add', {files: files});
        } else {
            uploadWidget.run();
        }
    }
}

/**
 * The `upload` option: the host uploads - the given files (pasted or dropped), or the ones
 * the user picks when none are given (`accept` narrowing the picker, e.g. `image/*`) - and
 * resolves with the uploaded files, which are inserted where the selection was. Resolving
 * with none (the user cancelled) or rejecting just removes the placeholder.
 */
const uploadThroughOption = function(context, id, files, accept = null) {
    const pending = context.options.upload(files ? Array.from(files) : null, {accept});

    if (!pending || typeof pending.then !== 'function') {
        return;
    }

    loaderStart(context, id, true);

    pending.then((uploaded) => {
        const nodes = (uploaded || []).map((file) => createNodeFromFile(context, file)).filter(Boolean);

        if (nodes.length) {
            replaceLoader(context, id, nodes, true);
        } else {
            removeLoader(context, id, true);
        }
    }).catch(() => {
        removeLoader(context, id, true);
    });
};

const createNodesFromResponse = function(context, response) {
    const nodes = [];

    // Otherwise, insert it at the placeholder's position, and remove the placeholder
    response.result.files.forEach((file) => {
        const node = createNodeFromFile(context, file);

        if (node) {
            nodes.push(node);
        }
    });

    return nodes;
};

export {
    triggerUpload,
    uploadThroughOption,
    createNodesFromResponse,
    createNodeFromFile,
}
