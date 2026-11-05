/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {schema} from './schema'
import {linkPlugin} from './plugin'
import {menu} from './menu'
import {DEFAULT_LINK_REL} from "../../util/linkUtil";
import {filterFileUrl} from "../../humhub-bridge";

const link = {
    id: 'link',
    schema: schema,
    menu: (context) => menu(context),
    registerMarkdownIt: (md) => {
        const defaultRender = md.renderer.rules.link_open || function(tokens, idx, options, env, self) {
            return self.renderToken(tokens, idx, options);
        };

        md.renderer.rules.link_open = function (tokens, idx, options, env, self) {
            const hrefIndex = tokens[idx].attrIndex('href');

            let {url, guid} = filterFileUrl(tokens[idx].attrs[hrefIndex][1], 'view');

            tokens[idx].attrs[hrefIndex][1] = url;

            if (guid) {
                tokens[idx].attrPush(['data-file-guid', guid]); // add new attribute
                tokens[idx].attrPush(['data-file-download', '']); // add new attribute
                tokens[idx].attrPush(['data-file-url', url]); // add new attribute
                tokens[idx].attrPush(['data-bs-target', '#globalModal']);
            }

            // If you are sure other plugins can't add `target` - drop check below
            const aIndex = tokens[idx].attrIndex('target');

            if (aIndex < 0) {
                // Check if the link is external
                const href = tokens[idx].attrs[hrefIndex][1];

                if (href[0] !== '#') {
                    const hrefUrl = new URL(href, window.location.href);
                    if (hrefUrl.hostname !== window.location.hostname || guid) {
                        tokens[idx].attrPush(['target', '_blank']); // add new attribute
                    }
                } else {
                    tokens[idx].attrPush(['target', '_self'])
                }
            } else if (!tokens[idx].attrs[aIndex][1]) {
                tokens[idx].attrs[aIndex][1] = '_blank'; // replace value of existing attr
            }

            tokens[idx].attrPush(['rel', DEFAULT_LINK_REL]);

            // pass token to default renderer.
            return defaultRender(tokens, idx, options, env, self);
        };
    },
    plugins: (context) => {
        return [linkPlugin(context)];
    }
};

export default link;
