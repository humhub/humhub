/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {schema} from './schema'
import {bulletListRule} from './input-rules'
import {menu} from './menu'

const bullet_list = {
    id: 'bullet_list',
    schema: schema,
    menu: (context) => menu(context),
    inputRules: (schema) => {return [bulletListRule(schema)]}
};

export default bullet_list;
