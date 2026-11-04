/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {textblockTypeInputRule} from "prosemirror-inputrules";

// : (NodeType) → InputRule
// Given a blockquote node type, returns an input rule that turns `"> "`
// at the start of a textblock into a blockquote.
const headingRule = (schema) => {
    const maxLevel = 6;
    return textblockTypeInputRule(
        new RegExp("^(#{1," + maxLevel + "})\\s$"),
        schema.nodes.heading,
        match => ({level: match[1].length})
    );
};

export {headingRule};