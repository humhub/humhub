/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {validateHref, DEFAULT_LINK_REL} from "../../util/linkUtil";
import {filterFileUrl} from "../../humhub-bridge";

const schema = {
    marks: {
        sortOrder: 300,
        link: {
            attrs: {
                href: {},
                title: {default: null},
                target: {default: '_blank'},
                fileGuid: { default: null},
                rel: {default: DEFAULT_LINK_REL}
            },
            inclusive: false,
            parseDOM:
                [{
                    tag: "a[href]", getAttrs: function getAttrs(dom) {
                        let href = dom.getAttribute("href");
                        if (!validateHref(href, {relative: true}))  {
                            href = '#';
                        }

                        return {
                            href: href,
                            title: dom.getAttribute("title"),
                            target: dom.getAttribute("target"),
                            fileGuid: dom.getAttribute("data-file-guid")
                        }
                    }
                }],
            toDOM(node) { let {href, title} = node.attrs; return ["a", {href, title}, 0] },
            parseMarkdown: {
                mark: "link", getAttrs: function (tok) {
                    let {url, guid} = filterFileUrl(tok.attrGet("href"), 'view');

                    if (!validateHref(url, {relative: true}))  {
                        url = '#';
                    }

                    return ({
                        href: url,
                        title: tok.attrGet("title") || null,
                        fileGuid: guid
                    });
                }
            },
            toMarkdown: {
                open: "[",
                close: function close(state, mark) {
                    let href = (mark.attrs.fileGuid) ? 'file-guid:'+mark.attrs.fileGuid  : mark.attrs.href;
                    return "](" + state.esc(href) + (mark.attrs.title ? " " + state.quote(mark.attrs.title) : "") + ")"
                }
            }
        }
    }
};

export {schema};
