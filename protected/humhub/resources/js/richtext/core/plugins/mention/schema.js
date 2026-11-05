import {$node} from "../../util/node"
import {schema as linkSchema} from "../link/schema"

const schema = {
    nodes: {
        mention: {
            inline: true,
            group: 'inline',
            selectable: true,
            draggable: true,
            attrs: {
                name: {default: ''},
                guid: {default: ''},
                href: {default: '#'},
            },
            parseDOM: [{
                tag: 'span[data-mention]',
                getAttrs: (dom) => {
                    return {
                        guid: dom.getAttribute('data-mention'),
                        name: dom.textContent,
                    };
                },
            }],
            toDOM(node) {
                const attrs = {
                    'data-mention': node.attrs.guid,
                    contentEditable: 'false',
                    style: 'display:inline-block'
                };

                return [
                    'span',
                    attrs,
                    ['span', {style: 'display:block'}, (node.attrs.name.charAt(0) === '@' ? '' : '@') + node.attrs.name]
                ];
            },
            parseMarkdown: {
                node: "mention",
                getAttrs: (tok) => {
                    return ({
                        name: tok.attrGet("name"),
                        guid: tok.attrGet("guid"),
                        href: tok.attrGet("href")
                    })
                }
            },
            toMarkdown: (state, node) => {
                let linkMark = $node(node).getMark('link');
                if (linkMark) {
                    state.write(linkSchema.marks.link.toMarkdown.close(state, linkMark));
                }

                let {guid, name, href} = node.attrs;
                state.write("[" + state.esc(name) + "](mention:" + state.esc(guid) + " " + state.quote(href) + ")");

                if (linkMark) {
                    state.write(linkSchema.marks.link.toMarkdown.open);
                }
            },
        }
    },
    marks: {
        mentionQuery: {
            excludes: "_",
            inclusive: true,
            parseDOM: [
                {tag: 'span[data-mention-query]'}
            ],
            toDOM(node) {
                return ['span', {
                    'data-mention-query': true,
                    style: `color: #0078D7`
                }];
            }
        }
    }
};

export {schema};
