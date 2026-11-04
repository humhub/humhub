import {buildLink} from "../../util/linkUtil";
import {getOembed} from "../../humhub-bridge";
import {validateHref} from "../../util/linkUtil";

const oembed = {
    attrs: {
        href: {},
    },
    marks: "",
    atom: true,
    draggable: true,
    inline: true,
    group: "inline",
    parseDOM: [{
        tag: "[data-oembed]", getAttrs: function getAttrs(dom) {

            return {
                href: dom.getAttribute("data-oembed")
            };
        }
    }],
    toDOM: (node) => {
        let href = node.attrs.href;
        let $oembed = getOembed(href);

        if ($oembed && $oembed.length) {
            return $oembed.show()[0];
        }

        if(!validateHref(href)) {
            href = '#';
        }

        return $(buildLink(href, {'class': 'not-found'}))[0];
    },
    parseMarkdown: {
        node: "oembed", getAttrs: function(tok) {
            return ({
                href: tok.attrGet("href")
            });
        }
    },
    toMarkdown: (state, node) => {
        state.write('['+node.attrs.href+'](oembed:'+node.attrs.href+')');
    }
};

const schema = {
    nodes: {
        oembed: oembed
    }
};

export {schema}