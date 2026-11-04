import twemoji from "twemoji";
import {getEmojiConfig} from "../../humhub-bridge";

const schema = {
    nodes: {
        emoji: {
            attrs: {
                class: {default: 'emoji'},
                draggable: {default: 'false'},
                width: {default: '16'},
                height: {default: '16'},
                'data-name': {default: null},
                alt: {default: null},
                src: {default: null},
            },
            inline: true,
            group: "inline",
            parseDOM: [{
                tag: "img.emoji",
                getAttrs: (dom) => {
                    return {
                        src: dom.getAttribute("src"),
                        alt: dom.getAttribute("alt"),
                        'data-name': String(dom.getAttribute('data-name'))
                    };
                }
            }],
            toDOM: (node) => {
                return ['img', node.attrs];
            },
            parseMarkdown: {
                node: "emoji",
                getAttrs: (tok) => {
                    // Workaround, since the context is not available here,
                    // so we can't use context.getPluginOption('emoji', 'twemoji');
                    const options = getEmojiConfig()['twemoji'];

                    let $dom = $(twemoji.parse(tok.content, options));
                    return ({
                        'data-name': String(tok.markup),
                        alt: $dom.attr('alt'),
                        src: $dom.attr('src')
                    });
                }
            },
            toMarkdown: (state, node) => {
                let result;

                if (!node.attrs['data-name']) {
                    result = (state.alt) ? state.esc(state.alt) : '';
                } else {
                    result = ':' + state.esc(node.attrs['data-name']) + ':';
                }

                state.write(result);
            }
        }
    },
    marks: {
        emojiQuery: {
            excludes: "_",
            inclusive: true,
            parseDOM: [
                {tag: 'span[data-emoji-query]'}
            ],
            toDOM(node) {
                return ['span', {
                    'data-emoji-query': true,
                }];
            }
        }
    }
};

export {schema};
