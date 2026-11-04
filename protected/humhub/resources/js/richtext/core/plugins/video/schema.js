/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {filterFileUrl} from "../../humhub-bridge";
import {validateHref} from "../../util/linkUtil";
import {getBooleanAttrsFromTokenFlags} from "../../../markdown/mediaOptions";
import {FLOAT_NONE, getAltExtensionByFloat, getClassForFloat} from "../image/imageFloat";

const BOOLEAN_VIDEO_ATTRS = ['controls', 'autoplay', 'muted', 'loop'];
const FILE_GUID_PREFIX = 'file-guid:';

const normalizeFileUrl = (raw, source) => {
    if (raw && typeof raw === 'object') {
        return raw;
    }

    if (typeof source === 'string' && source.indexOf(FILE_GUID_PREFIX) === 0) {
        return {
            url: source,
            guid: source.substring(FILE_GUID_PREFIX.length)
        };
    }

    return {
        url: source,
        guid: null
    };
};

const buildVideoDomAttrs = (attrs) => {
    const domAttrs = {
        src: attrs.src,
        title: attrs.title || null,
        width: attrs.width || null,
        height: attrs.height || null,
        class: getClassForFloat(attrs.float),
        'data-file-guid': attrs.fileGuid || null
    };

    BOOLEAN_VIDEO_ATTRS.forEach((attr) => {
        if (attrs[attr]) {
            domAttrs[attr] = attr;
        }
    });

    return domAttrs;
};

const schema = {
    nodes: {
        video: {
            sortOrder: 1010,
            group: "inline",
            inline: true,
            draggable: true,
            attrs: {
                src: {},
                title: {default: null},
                controls: {default: false},
                autoplay: {default: false},
                muted: {default: false},
                loop: {default: false},
                width: {default: null},
                height: {default: null},
                float: {default: FLOAT_NONE},
                fileGuid: {default: null}
            },
            parseDOM: [{
                tag: "video[src]",
                getAttrs: (dom) => {
                    return {
                        src: dom.getAttribute("src"),
                        title: dom.getAttribute("title"),
                        controls: dom.hasAttribute("controls"),
                        autoplay: dom.hasAttribute("autoplay"),
                        muted: dom.hasAttribute("muted"),
                        loop: dom.hasAttribute("loop"),
                        width: dom.getAttribute("width"),
                        height: dom.getAttribute("height"),
                        fileGuid: dom.getAttribute("data-file-guid")
                    };
                }
            }],
            toDOM: (node) => {
                return ['video', buildVideoDomAttrs(node.attrs)];
            },
            parseMarkdown: {
                video: {
                    node: "video",
                    getAttrs: (tok) => {
                        const src = tok.attrGet("src");
                        let {url, guid} = normalizeFileUrl(filterFileUrl(src), src);

                        if (!validateHref(url, {relative: true})) {
                            if (!guid) {
                                url = '#';
                            }
                        }

                        return Object.assign({
                            src: url,
                            title: tok.attrGet("alt") || tok.attrGet("title") || null,
                            width: tok.attrGet("width") || null,
                            height: tok.attrGet("height") || null,
                            float: tok.attrGet("float") || FLOAT_NONE,
                            fileGuid: guid
                        }, getBooleanAttrsFromTokenFlags(tok, BOOLEAN_VIDEO_ATTRS));
                    }
                }
            },
            toMarkdown: (state, node) => {
                const src = node.attrs.fileGuid ? 'file-guid:' + node.attrs.fileGuid : node.attrs.src;
                const options = ['video'];
                BOOLEAN_VIDEO_ATTRS.forEach((attr) => {
                    if (node.attrs[attr]) {
                        options.push(attr);
                    }
                });
                const title = (node.attrs.title || "") + getAltExtensionByFloat(node.attrs.float);

                let resizeAddition = "";
                if (node.attrs.width || node.attrs.height) {
                    resizeAddition += " =";
                    resizeAddition += (node.attrs.width) ? node.attrs.width : '';
                    resizeAddition += 'x';
                    resizeAddition += (node.attrs.height) ? node.attrs.height : '';
                }

                state.write("![" + state.esc(title) + "](" + state.esc(src) + " " + options.join(' ') + resizeAddition + ")");
            }
        }
    }
};

export {schema};
