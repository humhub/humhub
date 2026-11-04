/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
import {getFileHandlerItem, getFileHandlerContainer} from "./service";

export function menu(context) {
    const links = getFileHandlerContainer(context).find('a[data-action-process=file-handler]')

    if (links.length === 0) {
        return []
    }

    const isModalWindow = getFileHandlerContainer(context).closest('[data-ui-widget="ui.modal.Modal"]').length !== 0

    let menus = []
    for (let l = 0; l < links.length; l++) {
        const link = links.eq(l)

        if (isModalWindow && link.data('action-click') === 'ui.modal.load') {
            // Skip File Handler if it works through modal window and current editor is initialized from modal window as well,
            // because original modal window is replaced with the File Handler's modal window so impossible proper work there.
            continue
        }

        menus.push({
            id: 'insertFileHandler',
            node: 'file_handler',
            group: 'insert',
            item: getFileHandlerItem(context, link, l)
        })
    }

    return menus
}
