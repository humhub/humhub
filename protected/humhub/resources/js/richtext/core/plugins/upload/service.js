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
    createNodesFromResponse,
    createNodeFromFile,
}
