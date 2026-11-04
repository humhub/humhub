/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

const schema = {
    nodes: {
        hard_break: {
            sortOrder: 1100,
            inline: true,
            group: "inline",
            selectable: false,
            parseDOM: [{tag: "br"}],
            toDOM: () => {
                return ["br"]
            },
            parseMarkdown: {hardbreak: {node: "hard_break"}},
            toMarkdown: (state, node, parent, index) => {
                for (let i = index + 1; i < parent.childCount; i++) {
                    if (parent.child(i).type !== node.type) {
                        (state.table) ? state.write('<br>') : state.write("  \n");
                        return;
                    }
                }
            }
        }
    }
};

export {schema}