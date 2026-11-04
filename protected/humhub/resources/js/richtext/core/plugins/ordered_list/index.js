/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
import {schema} from './schema'
import {orderedListRule} from './input-rules'
import {menu} from './menu'

const ordered_list = {
    id: 'ordered_list',
    menu: (context) => menu(context),
    schema: schema,
    inputRules: (schema) => {return [orderedListRule(schema)]}
};

export default ordered_list;
