/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

const code_block = {
    content: "text*",
    group: "block",
    code: true,
    defining: true,
    isolating: true,
    marks: "",
    parseDOM: [{
        tag: "pre"
    }],
    toDOM: () => {
        return ["pre", 0];
    },
    parseMarkdown: {block: "code_block"},
    toMarkdown: (state, node) => {
        if (state.table) {
            state.wrapBlock("`", "`", node, () => {
                return state.text(node.textContent, false);
            });
        } else if (!node.attrs.params) {
            state.write("```\n");
            state.text(node.textContent, false);
            state.ensureNewLine();
            state.write("```");
            state.closeBlock(node);
        } else {
            state.write("```" + node.attrs.params + "\n");
            state.text(node.textContent, false);
            state.ensureNewLine();
            state.write("```");
            state.closeBlock(node);
        }
    }
};

const fence = {
    parseMarkdown: {
        block: "code_block",
        getAttrs: (tok) => ({params: tok.info || ""})
    }
};

const schema = {
    nodes: { code_block, fence }
};

export {schema};
