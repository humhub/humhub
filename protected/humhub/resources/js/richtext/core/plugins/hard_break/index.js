/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
import {schema} from './schema'
import {htmlBreak} from "./extension";

const hard_break = {
    id: 'hard_break',
    schema: schema,
    registerMarkdownIt: (markdownIt) => {
        markdownIt.inline.ruler.before('newline','htmlbreak', htmlBreak);
    }
};

export default hard_break;
