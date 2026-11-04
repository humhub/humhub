/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {schema} from './schema';
import {mentionRule} from './input-rules';
import {mentionPlugin} from './plugin';
import {keymap} from './keymap';
import {createLinkExtension} from "../../../markdown/linkExtensionTokenizer";
import {buildLink, validateHref} from "../../util/linkUtil";

const mention = {
    id: 'mention',
    schema: schema,
    plugins: (context) => {
        if (!context.options.mention || !context.options.mention.provider) {
            return [];
        }
        return [
            mentionPlugin(context)
        ];
    },
    inputRules: (schema) => {
        return [mentionRule(schema)];
    },
    keymap: (context) => keymap(),
    registerMarkdownIt: (markdownIt) => {
        // [name](mention:guid "href")
        markdownIt.inline.ruler.before('link', 'mention', createLinkExtension('mention', {
            labelAttr: 'name',
            hrefAttr: 'guid',
            titleAttr: 'href'
        }));

        markdownIt.renderer.rules.mention = function (token, idx) {
            let mentioning = token[idx];
            let href = mentioning.attrGet('href');
            let guid = mentioning.attrGet('guid');
            let label = '@' + mentioning.attrGet('name');

            if (!validateHref(href, {relative: true})) {
                return buildLink('#', {'class': 'not-found'}, label);
            }

            return buildLink(href, {'data-contentcontainer-guid': guid}, label, false);
        };
    }
};

export default mention;
