/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

const schema = {
    nodes: {
        list_item: {
            sortOrder: 800,
            content: "paragraph block*",
            defining: true,
            parseDOM: [{tag: "li"}],
            toDOM: () => {
                return ["li", 0]
            },
            parseMarkdown: {block: "list_item"},
            toMarkdown: (state, node) => {
                state.renderContent(node);
            }
        }
    }
};

export {schema};
