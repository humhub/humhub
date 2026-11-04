/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

import {isHumhub} from "../../humhub-bridge"
import {canInsertLink, MenuItem} from "../../menu"
import {loaderStart, replaceLoader, removeLoader} from "../loader/plugin"
import {createNodeFromFile, createNodesFromResponse} from "../upload/service"

const isActiveFileHandler = function () {
    return isHumhub() &&
        typeof humhub.prosemirrorFileHandler !== 'undefined' &&
        humhub.prosemirrorFileHandler === true;
}

const getFileHandlerItem = function(context, link, index) {
    link.on('click', function () {
        if (isHumhub()) {
            humhub.prosemirrorFileHandler = false
        }
    })

    return new MenuItem({
        label: link.html(),
        title: link.text(),
        sortOrder: 300 + index,
        enable(state) {
            return canInsertLink(state)
        },
        run() {
            link.click()
            if (isHumhub()) {
                humhub.prosemirrorFileHandler = true
            }
        }
    })
}

const initFileHandler = function(context) {
    if (!isHumhub()) {
        return
    }

    humhub.event.on('humhub:file:created', (evt, file) => {
        if (isActiveFileHandler() && typeof context.editor.view !== 'undefined') {
            const view = context.editor.view
            const node = createFileHandlerNode(context, file)
            if (node) {
                view.dispatch(view.state.tr.replaceSelectionWith(node, false));
            }
        }
    })

    const uploadWidget = humhub.require('ui.widget').Widget.instance(getFileHandlerContainer(context).find('[data-ui-widget="file.Upload"]').last())
    if (uploadWidget) {
        const id = context.id + '-file-handler'
        uploadWidget.off('uploadStart.richtext').on('uploadStart.richtext', () => {
            if (isActiveFileHandler()) {
                loaderStart(context, id, true)
            }
        }).off('uploadEnd.richtext').on('uploadEnd.richtext', (evt, response) => {
            if (isActiveFileHandler()) {
                replaceLoader(context, id, createNodesFromResponse(context, response), true)
            }
        }).off('uploadFinish.richtext').on('uploadFinish.richtext', () => {
            if (isActiveFileHandler()) {
                removeLoader(context, id, true)
            }
        })
    }
}

const createFileHandlerNode = function(context, file) {
    return createNodeFromFile(context, file)
}

const getFileHandlerContainer = function (context) {
    const form = context.editor.$.closest('form')
    const uploadButton = form.find('#' + context.id + '-file-upload').closest('.btn-group')
    return uploadButton.length ? uploadButton : form
}

export {
    initFileHandler,
    getFileHandlerItem,
    getFileHandlerContainer,
}
