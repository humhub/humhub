import {Plugin} from 'prosemirror-state';
import {Slice, Fragment} from "prosemirror-model";

import {editNode} from './menu';
import {buildLink} from "../../util/linkUtil";

let linkPlugin = (context) => {
    return new Plugin({
        props: {
            nodeViews: {
                link(node) { return new LinkView(node, context) }
            },
            transformPasted: (slice) => {
                return new Slice(linkify(slice.content, context), slice.openStart, slice.openEnd);
            }
        }
    });
};

class LinkView {
    constructor(mark, context) {
        // The editor will use this as the node's DOM representation
        this.createDom(mark);
        this.dom.addEventListener("click", (e) => editNode(this.dom, context));
    }

    createDom(mark) {
        this.dom = $(buildLink(mark.attrs.href, {
            'data-file-guid': mark.attrs.fileGuid,
            target: mark.attrs.target || '_blank'
        }, null, {relative: true}))[0];
    }

    stopEvent() { return true }
}

const clean = (val) => {
    return (val) ? val.replace(/(["'])/g, '') : val;
};

// eslint-disable-next-line
const HTTP_LINK_REGEX = /((https?:\/\/(?:www\.|(?!www))[^\s".']+\.[^\s".']{2,})|[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6})/ig;

const linkify = function(fragment, context) {
    let linkified = [];
    let urls = [];
    fragment.forEach((child) => {
        if (child.isText) {
            const text = child.text;
            let pos = 0, match;

            while (match = HTTP_LINK_REGEX.exec(text)) {
                let start = match.index;
                let end = start + match[0].length;
                let link = child.type.schema.marks['link'];

                // simply copy across the text from before the match
                if (start > 0) {
                    linkified.push(child.cut(pos, start));
                }

                let urlText = text.slice(start, end);

                if (urlText.indexOf('http') !== 0) {
                    urlText = 'mailto:'+urlText;
                }

                urls.push(urlText);
                linkified.push(
                    child.cut(start, end).mark(link.create({href: urlText}).addToSet(child.marks))
                );
                pos = end;
            }

            // copy over whatever is left
            if (pos < text.length) {
                linkified.push(child.cut(pos));
            }
        } else {
            linkified.push(child.copy(linkify(child.content, context)));
        }
    });

    if (urls.length) {
        context.event.trigger('linkified', [urls, linkified]);
    }
    return Fragment.fromArray(linkified);
};

export {linkPlugin};
