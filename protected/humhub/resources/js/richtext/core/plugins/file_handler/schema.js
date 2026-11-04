/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {validateHref} from "../../util/linkUtil";
import {filterFileUrl} from "../../humhub-bridge";

const schema = {
    nodes: {
        file_handler: {}
    },
    marks: {
        sortOrder: 100,
        link: {
            attrs: {
                href: {},
                fileGuid: {default: null}
            },
            inclusive: false,
            parseDOM: [{
                tag: "a[href]", getAttrs: function getAttrs(dom) {
                    let href = dom.getAttribute("href");
                    if (!validateHref(href, {relative: true}))  {
                        href = '#';
                    }

                    return {
                        href: href,
                        fileGuid: dom.getAttribute("data-file-guid")
                    }
                }
            }],
            toDOM(node) { let {href} = node.attrs; return ["a", {href}, 0] },
            parseMarkdown: {
                mark: "link",
                getAttrs: function (tok) {
                    let {url, guid} = filterFileUrl(tok.attrGet("href"), 'view');

                    if (!validateHref(url, {relative: true}))  {
                        url = '#';
                    }

                    return ({
                        href: url,
                        fileGuid: guid
                    });
                }
            },
            toMarkdown: {
                open: "[",
                close: function close(state, mark) {
                    let href = (mark.attrs.fileGuid) ? 'file-guid:' + mark.attrs.fileGuid  : mark.attrs.href;
                    return "](" + state.esc(href) + ")"
                }
            }
        }
    }
};

export {schema}
