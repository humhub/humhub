/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {schema} from './schema';
import {blockquoteRule} from './input-rules';
import {menu} from "./menu";

const blockquote = {
    id: 'blockquote',
    schema: schema,
    menu: (context) => menu(context),
    inputRules: (schema) => {
        return [blockquoteRule(schema)]
    }
};

export default blockquote;
