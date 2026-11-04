/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
import {focusPlugin} from './plugin';

const focus = {
    id: 'focus',
    plugins: (context) => {
        return [
            focusPlugin(context)
        ];
    },
};

export default focus;
