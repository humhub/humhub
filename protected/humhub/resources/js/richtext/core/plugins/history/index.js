/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
import {history} from "prosemirror-history"
import {menu} from "./menu"

const historyPlugin = {
    id: 'history',
    menu: (context) => menu(context),
    plugins: (context) => {
        return [
            history()
        ]
    }
}

export default historyPlugin;
