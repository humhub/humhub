/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {savePlugin} from './plugin';

const mention = {
    id: 'save',
    plugins: (context) => {
        if (!context.options.mention || !context.options.mention.provider) {
            return [];
        }
        return [
            savePlugin(context)
        ];
    },
};

export default mention;
