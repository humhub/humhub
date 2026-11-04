/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
const schema = {
    nodes: {
        bullet_list: {
            sortOrder: 700,
            content: "list_item+",
            group: "block",
            attrs: {level: {default: 1}, tight: {default: true}},
            parseDOM: [{
                tag: "ul", getAttrs: function (dom) {
                    return ({level: 1, tight: dom.hasAttribute("data-tight")});
                }
            }],
            toDOM: (node) => {
                return ["ul", {"data-tight": node.attrs.tight ? "true" : null}, 0]
            },
            parseMarkdown: {block: "bullet_list"},
            toMarkdown: (state, node) => {
                state.renderList(node, "  ", function () { return (node.attrs.bullet || "-") + " "; });
            }
        }
    }
};

export {schema}
