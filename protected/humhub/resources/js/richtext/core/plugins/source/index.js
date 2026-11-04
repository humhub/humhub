/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
import {menu, menuWrapper} from "./menu"
import {sourcePlugin} from "./plugin"

const source = {
    id: 'source',
    menu: (context) => menu(context),
    menuWrapper: (context) => menuWrapper(context),
    plugins: (context) => {
        return [
            sourcePlugin(context)
        ];
    },
};

export default source;
