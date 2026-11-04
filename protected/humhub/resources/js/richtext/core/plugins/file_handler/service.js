/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

import {isHumhub} from "../../humhub-bridge"
import {canInsertLink, MenuItem} from "../../menu"
import {loaderStart, replaceLoader, removeLoader} from "../loader/plugin"
import {createNodeFromFile, createNodesFromResponse, uploadThroughOption} from "../upload/service"

// The platform's own handlers ("Attach an image", ...): open the picker for one type
const UPLOAD_BY_TYPE_ACTION = 'file.uploadByType';

const acceptOf = function(link) {
    const params = link.data('action-params');
    return (params && params.type) || null;
}

// The editor whose menu ran a handler last: the one the file the handler creates belongs to.
// Any handler entry clicked (an editor's, or one of an upload field next to it) resets it, so
// a file is never claimed by an editor whose handler was abandoned.
let activeContext = null

$(document).on('click.richtextFileHandler', 'a[data-action-process=file-handler]', () => {
    activeContext = null
})

const isActiveFileHandler = function (context) {
    return activeContext === context
}

const getFileHandlerItem = function(context, link, index) {
    return new MenuItem({
        label: link.html(),
        title: link.text(),
        sortOrder: 300 + index,
        enable(state) {
            return canInsertLink(state)
        },
        run() {
            // With the `upload` option the editor uploads itself - the legacy action would
            // look for the upload button next to the editor, which a host using the option
            // does not render
            if (typeof context.options.upload === 'function' && link.data('action-click') === UPLOAD_BY_TYPE_ACTION) {
                uploadThroughOption(context, {}, null, acceptOf(link))
                return
            }

            link.click()
            activeContext = context
        }
    })
}

const initFileHandler = function(context) {
    if (!isHumhub()) {
        return
    }

    humhub.event.on('humhub:file:created', (evt, file) => {
        if (isActiveFileHandler(context) && typeof context.editor.view !== 'undefined') {
            const view = context.editor.view
            const node = createFileHandlerNode(context, file)
            if (node) {
                view.dispatch(view.state.tr.replaceSelectionWith(node, false));
            }
            // Taken: a file a handler outside the editor creates later is not the editor's
            activeContext = null
        }
    })

    const uploadWidget = humhub.require('ui.widget').Widget.instance(getFileHandlerContainer(context).find('[data-ui-widget="file.Upload"]').last())
    if (uploadWidget) {
        const id = context.id + '-file-handler'
        uploadWidget.off('uploadStart.richtext').on('uploadStart.richtext', () => {
            if (isActiveFileHandler(context)) {
                loaderStart(context, id, true)
            }
        }).off('uploadEnd.richtext').on('uploadEnd.richtext', (evt, response) => {
            if (isActiveFileHandler(context)) {
                replaceLoader(context, id, createNodesFromResponse(context, response), true)
            }
        }).off('uploadFinish.richtext').on('uploadFinish.richtext', () => {
            if (isActiveFileHandler(context)) {
                removeLoader(context, id, true)
            }
        })
    }
}

const createFileHandlerNode = function(context, file) {
    return createNodeFromFile(context, file)
}

const getFileHandlerContainer = function (context) {
    // `fileHandlers` option: the element holding the handler links, or a function returning it
    let fileHandlers = context.options.fileHandlers
    if (typeof fileHandlers === 'function') {
        fileHandlers = fileHandlers()
    }
    if (fileHandlers) {
        return $(fileHandlers)
    }

    const form = context.editor.$.closest('form')
    const uploadButton = form.find('#' + context.id + '-file-upload').closest('.btn-group')
    return uploadButton.length ? uploadButton : form
}

export {
    initFileHandler,
    getFileHandlerItem,
    getFileHandlerContainer,
}
