/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

// Process ![test]( x =100x200)
//                    ^^^^^^^^ this size specification

const imageFloat = require('./imageFloat')
const parseImageSize = require('./parse_image_size');

const MEDIA_TOKEN_MAP = {
    video: 'video',
    audio: 'audio'
};

function parseMediaOptions(src, pos, max) {
    const result = {
        ok: false,
        pos: pos,
        options: ''
    };

    if (pos >= max || src.charCodeAt(pos) !== 0x7b /* { */) {
        return result;
    }

    const endPos = src.indexOf('}', pos + 1);
    if (endPos < 0 || endPos > max) {
        return result;
    }

    const raw = src.slice(pos + 1, endPos).trim();
    if (!raw.length) {
        return result;
    }

    result.ok = true;
    result.pos = endPos + 1;
    result.options = raw;
    return result;
}

function parseOptionFlags(rawOptions) {
    return rawOptions.trim().split(/\s+/).filter(Boolean);
}

function parseMediaOptionsWithoutBraces(src, pos, max) {
    const result = {
        ok: false,
        pos: pos,
        options: ''
    };

    let pointer = pos;
    let flags = [];

    while (pointer < max) {
        let code = src.charCodeAt(pointer);

        if (code === 0x20 || code === 0x0A) {
            pointer++;
            continue;
        }

        if (code === 0x29 /* ) */ || code === 0x3d /* = */) {
            break;
        }

        let start = pointer;
        while (pointer < max) {
            code = src.charCodeAt(pointer);
            if (code === 0x20 || code === 0x0A || code === 0x29 || code === 0x3d) {
                break;
            }
            pointer++;
        }

        const flag = src.slice(start, pointer).trim();
        if (!flag.length) {
            break;
        }

        flags.push(flag);
    }

    if (!flags.length) {
        return result;
    }

    result.options = flags.join(' ');
    if (!resolveMediaTokenType(result.options)) {
        return result;
    }

    result.ok = true;
    result.pos = pointer;
    return result;
}

function resolveMediaTokenType(rawOptions) {
    const flags = parseOptionFlags(rawOptions);
    for (let i = 0; i < flags.length; i++) {
        if (MEDIA_TOKEN_MAP[flags[i]]) {
            return MEDIA_TOKEN_MAP[flags[i]];
        }
    }

    return null;
}

function pushUniqueAttr(attrs, key, value) {
    for (let i = 0; i < attrs.length; i++) {
        if (attrs[i][0] === key) {
            return;
        }
    }
    attrs.push([key, value]);
}

function image_with_size(md, options) {
    return function (state, silent) {
        let attrs,
            code,
            label,
            labelEnd,
            labelStart,
            pos,
            ref,
            res,
            title,
            width = '',
            height = '',
            mediaOptions = '',
            token,
            tokens,
            start,
            href = '',
            oldPos = state.pos,
            max = state.posMax;

        if (state.src.charCodeAt(state.pos) !== 0x21/* ! */) {
            return false;
        }
        if (state.src.charCodeAt(state.pos + 1) !== 0x5B/* [ */) {
            return false;
        }

        labelStart = state.pos + 2;
        labelEnd = md.helpers.parseLinkLabel(state, state.pos + 1, false);

        // parser failed to find ']', so it's not a valid link
        if (labelEnd < 0) {
            return false;
        }

        pos = labelEnd + 1;
        if (pos < max && state.src.charCodeAt(pos) === 0x28) {
            // Inline link

            // [link](  <href>  "title"  )
            //        ^^ skipping these spaces
            pos++;
            for (; pos < max; pos++) {
                code = state.src.charCodeAt(pos);
                if (code !== 0x20 && code !== 0x0A) {
                    break;
                }
            }
            if (pos >= max) {
                return false;
            }

            // [link](  <href>  "title"  )
            //          ^^^^^^ parsing link destination
            start = pos;
            res = md.helpers.parseLinkDestination(state.src, pos, state.posMax);
            if (res.ok) {
                href = state.md.normalizeLink(res.str);
                if (state.md.validateLink(href)) {
                    pos = res.pos;
                } else {
                    href = '';
                }
            }

            // [link](  <href>  "title"  )
            //                ^^ skipping these spaces
            start = pos;
            for (; pos < max; pos++) {
                code = state.src.charCodeAt(pos);
                if (code !== 0x20 && code !== 0x0A) {
                    break;
                }
            }

            // [link](  <href>  "title"  )
            //                  ^^^^^^^ parsing link title
            res = md.helpers.parseLinkTitle(state.src, pos, state.posMax);
            if (pos < max && start !== pos && res.ok) {
                title = res.str;
                pos = res.pos;

                // [link](  <href>  "title"  )
                //                         ^^ skipping these spaces
                for (; pos < max; pos++) {
                    code = state.src.charCodeAt(pos);
                    if (code !== 0x20 && code !== 0x0A) {
                        break;
                    }
                }
            } else {
                title = '';
            }

            // [link](  <href>  "title" {video ...} =WxH  )
            //                         ^^^^^^^^^^^ ^^^^ parse optional media options and image size
            while (pos - 1 >= 0) {
                let hasChange = false;
                code = state.src.charCodeAt(pos - 1);

                // there must be at least one white spaces between previous field and the optional extension.
                if (code !== 0x20) {
                    break;
                }

                res = parseMediaOptions(state.src, pos, state.posMax);
                if (res.ok) {
                    mediaOptions = res.options;
                    pos = res.pos;
                    hasChange = true;

                    for (; pos < max; pos++) {
                        code = state.src.charCodeAt(pos);
                        if (code !== 0x20 && code !== 0x0A) {
                            break;
                        }
                    }
                }

                if (!mediaOptions) {
                    res = parseMediaOptionsWithoutBraces(state.src, pos, state.posMax);
                    if (res.ok) {
                        mediaOptions = res.options;
                        pos = res.pos;
                        hasChange = true;

                        for (; pos < max; pos++) {
                            code = state.src.charCodeAt(pos);
                            if (code !== 0x20 && code !== 0x0A) {
                                break;
                            }
                        }
                    }
                }

                res = parseImageSize(state.src, pos, state.posMax);
                if (res.ok) {
                    width = res.width;
                    height = res.height;
                    pos = res.pos;
                    hasChange = true;

                    for (; pos < max; pos++) {
                        code = state.src.charCodeAt(pos);
                        if (code !== 0x20 && code !== 0x0A) {
                            break;
                        }
                    }
                }

                if (!hasChange) {
                    break;
                }
            }

            if (pos >= max || state.src.charCodeAt(pos) !== 0x29/* ) */) {
                state.pos = oldPos;
                return false;
            }
            pos++;

        } else {
            // Link reference
            if (typeof state.env.references === 'undefined') {
                return false;
            }

            // [foo]  [bar]
            //      ^^ optional whitespace (can include newlines)
            for (; pos < max; pos++) {
                code = state.src.charCodeAt(pos);
                if (code !== 0x20 && code !== 0x0A) {
                    break;
                }
            }

            if (pos < max && state.src.charCodeAt(pos) === 0x5B/* [ */) {
                start = pos + 1;
                pos = md.helpers.parseLinkLabel(state, pos);
                if (pos >= 0) {
                    label = state.src.slice(start, pos++);
                } else {
                    pos = labelEnd + 1;
                }
            } else {
                pos = labelEnd + 1;
            }

            // covers label === '' and label === undefined
            // (collapsed reference link and shortcut reference link respectively)
            if (!label) {
                label = state.src.slice(labelStart, labelEnd);
            }

            ref = state.env.references[md.utils.normalizeReference(label)];
            if (!ref) {
                state.pos = oldPos;
                return false;
            }
            href = ref.href;
            title = ref.title;
        }

        // We found the end of the link, and know for a fact it's a valid link;
        // so all that's left to do is to call tokenizer.
        if (!silent) {
            state.pos = labelStart;
            state.posMax = labelEnd;

            const newState = new state.md.inline.State(
                state.src.slice(labelStart, labelEnd),
                state.md,
                state.env,
                tokens = []
            );
            newState.md.inline.tokenize(newState);

            // if 'autofill' option is specified
            // and width/height are both blank,
            // they are filled automatically
            if (options) {
                if (options.autofill && width === '' && height === '') {
                    try {
                        const dimensions = sizeOf(href);
                        width = dimensions.width;
                        height = dimensions.height;
                    } catch (e) {
                        /* empty */
                    }
                }
            }

            const mediaTokenType = mediaOptions ? resolveMediaTokenType(mediaOptions) : null;
            const tokenType = mediaTokenType || 'image';
            const tokenTag = mediaTokenType || 'img';

            token = state.push(tokenType, tokenTag, 0);
            token.attrs = attrs = [['src', href]];
            token.children = tokens;

            // Parse image float extension
            let altTextToken = tokens.length ? tokens[tokens.length - 1] : null;

            if (altTextToken) {
                let {float, alt} = imageFloat.parseFloatFromAlt(altTextToken['content']);
                altTextToken['content'] = alt;
                token.attrs.push(['float', float]);
                token.attrs.push(['alt', alt]);
            } else {
                token.attrs.push(['float', imageFloat.FLOAT_NONE]);
                token.attrs.push(['alt', '']);
            }

            if (title) {
                attrs.push(['title', title]);
            }

            if (mediaOptions) {
                attrs.push(['media-options', mediaOptions]);
                if (mediaTokenType) {
                    parseOptionFlags(mediaOptions).forEach((flag) => {
                        pushUniqueAttr(attrs, flag, flag);
                    });
                }
            }

            if (width !== '') {
                attrs.push(['width', width]);
            }

            if (height !== '') {
                attrs.push(['height', height]);
            }
        }

        state.pos = pos;
        state.posMax = max;
        return true;
    };
}

module.exports = function imsize_plugin(md, options) {
    md.inline.ruler.before('emphasis', 'image', image_with_size(md, options));
};
