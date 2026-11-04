/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
import {schema} from './schema'
import {menu} from './menu'
import {strongRule} from './input-rules'

const strong = {
    id: 'strong',
    schema: schema,
    menu: (context) => menu(context),
    inputRules: (schema) => [strongRule(schema)],
};

export default strong;
