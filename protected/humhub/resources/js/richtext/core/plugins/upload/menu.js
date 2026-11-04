/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

import {MenuItem, canInsertLink} from "../../menu"
import {triggerUpload} from "./service"

let uploadFile = (context) => {
    return new MenuItem({
        title: context.translate("Upload and include a File"),
        label: '<i class="fa fa-upload"></i>' + context.translate("Upload File"),
        sortOrder: 0,
        enable(state) {
            return canInsertLink(state);
        },
        run(state, dispatch, view) {
            if (view.state.selection.$from.parent.inlineContent) {
                triggerUpload(state, view, context);
            }
        }
    });
};

export function menu(context) {
    return [
        {
            id: 'uploadFile',
            mark: 'link',
            group: 'insert',
            item: uploadFile(context)
        }
    ]
}
