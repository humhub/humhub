/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

import {menu, menuWrapper} from "./menu"
import {toggleNavPlugin} from "./plugin"

const resizeNav = {
    id: 'resizeNav',
    menu: (context) => menu(context),
    menuWrapper: (context) => menuWrapper(context),
    plugins: (context) => {
        return [
            toggleNavPlugin(context)
        ]
    }
}

export default resizeNav;
