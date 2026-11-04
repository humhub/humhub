const schema = {
    nodes: {
        paragraph:  {
            content: "inline*",
            group: "block",
            parseDOM: [{tag: "p"}],
            toDOM: () => {
                return ["p", 0]
            },
            parseMarkdown: {block: "paragraph"},
            toMarkdown: (state, node, parent) => {
                state.renderInline(node);

                if (!state.table) {
                    state.closeBlock(node);
                } else if (node.content && node.content.size && parent.lastChild !== node) {
                    state.write('<br><br>');
                }
            }
        }
    }
};

export {schema};
