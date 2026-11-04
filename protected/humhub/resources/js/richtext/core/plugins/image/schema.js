/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {getAltExtensionByFloat, FLOAT_NONE} from './imageFloat';
import {filterFileUrl} from "../../humhub-bridge";
import {validateHref} from "../../util/linkUtil";

const schema = {
    nodes: {
        image: {
            sortOrder: 1000,
            group: "inline",
            inline: true,
            draggable: true,
            attrs: {
                src: {},
                alt: {default: null},
                title: {default: null},
                width: {default: null},
                height: {default: null},
                float: {default: FLOAT_NONE},
                fileGuid: {default: null},
            },
            parseDOM: [{
                tag: "img[src]",
                getAttrs: (dom) => {
                    return {
                        src: dom.getAttribute("src"),
                        title: dom.getAttribute("title"),
                        alt: dom.getAttribute("alt"),
                        width: dom.getAttribute("width"),
                        height: dom.getAttribute("height"),
                        fileGuid: dom.getAttribute("data-file-guid")
                    }
                }
            }],
            toDOM: (node) => {
                return ['img', node.attrs];
            },
            parseMarkdown: {
                node: "image",
                getAttrs: (tok) => {
                    let {url, guid} = filterFileUrl(tok.attrGet("src"));

                    if (!validateHref(url, {relative: true})) {
                        url = '#';
                    }
                    return ({
                        src: url,
                        title: tok.attrGet("title") || null,
                        width: tok.attrGet("width") || null,
                        height: tok.attrGet("height") || null,
                        alt: tok.attrGet("alt") || null,
                        float: tok.attrGet("float") || FLOAT_NONE,
                        fileGuid: guid
                    });
                }
            },
            toMarkdown: (state, node) => {
                let resizeAddition = "";

                if (node.attrs.width || node.attrs.height) {
                    resizeAddition += " =";
                    resizeAddition += (node.attrs.width) ? node.attrs.width : '';
                    resizeAddition += 'x';
                    resizeAddition += (node.attrs.height) ? node.attrs.height : '';
                }

                let src = (node.attrs.fileGuid) ? 'file-guid:' + node.attrs.fileGuid : node.attrs.src;
                let float = getAltExtensionByFloat(node.attrs.float);

                state.write("![" + state.esc(node.attrs.alt || "") + float + "](" + state.esc(src) +
                    (node.attrs.title ? " " + state.quote(node.attrs.title) : "") + resizeAddition + ")");
            }
        }
    }
};

export {schema};
