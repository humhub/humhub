/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
import {schema} from './schema'
import {menu} from './menu'

const list_item = {
    id: 'list_item',
    schema: schema,
    menu: (context) => {
        return menu(context);
    },
};

export default list_item;
