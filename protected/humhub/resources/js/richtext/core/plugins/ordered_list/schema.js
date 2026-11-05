/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

const schema = {
    nodes: {
        ordered_list: {
            sortOrder: 600,
            content: "list_item+",
            group: "block",
            attrs: {level: {default: 1}, order: {default: 1}, tight: {default: true}},
            parseDOM: [{
                tag: "ol", getAttrs: function getAttrs(dom) {
                    return {
                        level: 1,
                        order: dom.hasAttribute("start") ? +dom.getAttribute("start") : 1,
                        tight: dom.hasAttribute("data-tight")
                    }
                }
            }],
            toDOM: (node) => {
                return ["ol", {
                    start: node.attrs.order == 1 ? null : node.attrs.order,
                    "data-tight": node.attrs.tight ? "true" : null
                }, 0]
            },
            parseMarkdown: {
                block: "ordered_list", getAttrs: function (tok) {
                    return ({order: +tok.attrGet("start") || 1});
                }
            },
            toMarkdown: (state, node) => {
                if (state.table) {
                    state.text(node.textContent);
                    return;
                }

                let start = node.attrs.order || 1;
                let maxW = String(start + node.childCount - 1).length;
                let space = state.repeat(" ", maxW + 2);
                state.renderList(node, space, function (i) {
                    let nStr = String(start + i);
                    return state.repeat(" ", maxW - nStr.length) + nStr + ". "
                });
            }
        }
    }
};

export {schema};
