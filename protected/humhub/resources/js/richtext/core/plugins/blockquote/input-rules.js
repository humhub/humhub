import {wrappingInputRule} from "prosemirror-inputrules";

// : (NodeType) → InputRule
// Given a blockquote node type, returns an input rule that turns `"> "`
// at the start of a textblock into a blockquote.
const blockquoteRule = (schema) => wrappingInputRule(/^\s*>\s$/, schema.nodes.blockquote);

export {blockquoteRule};
