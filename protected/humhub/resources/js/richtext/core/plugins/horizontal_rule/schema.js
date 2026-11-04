/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

const schema = {
    nodes: {
        horizontal_rule: {
            sortOrder: 300,
            group: "block",
            parseDOM: [{tag: "hr"}],
            toDOM: () => {
                return ["div", ["hr"]]
            },
            parseMarkdown: {hr: {node: "horizontal_rule"}},
            toMarkdown: (state, node) => {
                state.write(node.attrs.markup || "---");
                state.closeBlock(node);
            }
        }
    }
};

export {schema}
