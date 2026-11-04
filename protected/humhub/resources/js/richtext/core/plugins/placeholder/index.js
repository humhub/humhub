/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
import {placeholderPlugin} from './plugin'

const placeholder = {
    id: 'placeholder',
    plugins: (context) => {
        if(!context.options.placeholder || !context.options.placeholder.text) {
            return [];
        }

        return [
            placeholderPlugin(context)
        ];
    },
};

export default placeholder;
