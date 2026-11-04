/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {schema} from './schema';
import {menu} from './menu';
import {keymap} from './keymap';
import {codeBlockPlugin} from "./plugin";
import {codeBlockRule} from './input-rules';

const code_block = {
    id: 'code_block',
    schema,
    menu: (context) => menu(context),
    inputRules: (schema) => {return [codeBlockRule(schema)]},
    keymap: () => keymap(),
    plugins: (context) => {
        return [codeBlockPlugin(context)];
    }
};

export default code_block;
