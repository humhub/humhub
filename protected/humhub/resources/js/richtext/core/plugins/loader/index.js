/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

import {loaderPlugin} from './plugin'

const loader = {
    id: 'loader',
    plugins: (context) => {
        return [loaderPlugin(context)]
    },
};

export default loader;
