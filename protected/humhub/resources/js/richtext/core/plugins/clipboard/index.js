/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

import {clipboardPlugin} from "./plugin"

const clipboard = {
    id: 'clipboard',
    plugins: (context) => {
        return [
            clipboardPlugin(context)
        ]
    },
};

export default clipboard;
