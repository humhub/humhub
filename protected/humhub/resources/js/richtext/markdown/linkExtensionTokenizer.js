/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

// Process [link](oembed:<to> "stuff")

import MarkdownIt from "markdown-it"

let inst = new MarkdownIt();
let isSpace = inst.utils.isSpace;

function createLinkExtension(id, options) {
    options = options || {};

    options.node = options.node || 'a';
    options.hrefAttr = options.hrefAttr || 'href';
    options.titleAttr = options.titleAttr || 'title';
    options.labelAttr = options.labelAttr || 'label';

    return (state, silent) => {
        let attrs,
            code,
            labelEnd,
            labelStart,
            label,
            pos,
            res,
            title,
            token,
            href = '',
            prefix = id + ':',
            oldPos = state.pos,
            max = state.posMax,
            start = state.pos;

        if (state.src.charCodeAt(state.pos) !== 0x5B/* [ */) {
            return false;
        }

        labelStart = state.pos + 1;
        labelEnd = state.md.helpers.parseLinkLabel(state, state.pos, true);

        // parser failed to find ']', so it's not a valid link
        if (labelEnd < 0) {
            return false;
        }

        pos = labelEnd + 1;
        if (pos < max && state.src.charCodeAt(pos) === 0x28/* ( */) {
            // Inline link

            // [link](  <id>:<href>  "title"  )
            //        ^^ skipping these spaces
            pos++;
            for (; pos < max; pos++) {
                code = state.src.charCodeAt(pos);
                if (!isSpace(code) && code !== 0x0A) {
                    break;
                }
            }
            if (pos >= max) {
                return false;
            }

            // [link](  <id>:<href>  "title"  )
            //          ^^^^ parsing prefix
            for (let i = 0; i < prefix.length; i++) {
                if (state.src.charAt(pos++) !== prefix.charAt(i)) {
                    return false;
                }
            }

            // [link](  <id>:<href>  "title"  )
            //               ^^^^ parsing href
            res = state.md.helpers.parseLinkDestination(state.src, pos, state.posMax);

            if (res.ok) {
                href = state.md.normalizeLink(res.str);
                if (state.md.validateLink(href)) {
                    pos = res.pos;
                } else {
                    href = '';
                }
            }

            // [link](  <id>:<href>  "title"  )
            //                     ^^ skipping these spaces
            start = pos;
            for (; pos < max; pos++) {
                code = state.src.charCodeAt(pos);
                if (!isSpace(code) && code !== 0x0A) {
                    break;
                }
            }

            // [link](  <id>:<href>  "title"  )
            //                       ^^^^^^^ parsing link title
            res = state.md.helpers.parseLinkTitle(state.src, pos, state.posMax);
            if (pos < max && start !== pos && res.ok) {
                title = res.str;
                pos = res.pos;

                // [link](  <id>:<href>  "title"  )
                //                              ^^ skipping these spaces
                for (; pos < max; pos++) {
                    code = state.src.charCodeAt(pos);
                    if (!isSpace(code) && code !== 0x0A) {
                        break;
                    }
                }
            } else {
                title = '';
            }

            if (pos >= max || state.src.charCodeAt(pos) !== 0x29/* ) */) {
                // parsing a valid shortcut link failed
                return false;
            }
            pos++;
        } else {
            return false;
        }

        // We found the end of the link, and know for a fact it's a valid link;
        // so all that's left to do is to call tokenizer.
        if (!silent) {
            state.pos = labelStart;
            state.posMax = labelEnd;

            label = state.src.substring(labelStart, labelEnd);

            token = state.push(id, options.node, 0);
            token.attrs = attrs = [[options.hrefAttr, href]];

            if (label) {
                attrs.push([options.labelAttr, label]);
            }

            if (title) {
                attrs.push([options.titleAttr, title]);
            }

            while (state.src.charCodeAt(state.pos) !== 0x29/* ) */) {
                state.pos++;
            }

            // NOTE linkExtensions currently do not support inline formatting:
            // TODO: make _open, _close behavior optional in order to support inline label format state.md.inline.tokenize(state);
        }

        state.pos = pos;
        state.posMax = max;
        return true;
    };
}

export {createLinkExtension};
