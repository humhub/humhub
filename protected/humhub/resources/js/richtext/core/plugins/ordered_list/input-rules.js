/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

import { wrappingInputRule} from "prosemirror-inputrules"

// : (NodeType) → InputRule
// Given a list node type, returns an input rule that turns a number
// followed by a dot at the start of a textblock into an ordered list.
let orderedListRule = function(schema) {
    return wrappingInputRule(/^(\d+)\.\s$/, schema.nodes.ordered_list, match => ({order: +match[1]}),
        (match, node) => node.childCount + node.attrs.order == +match[1])
};

export {orderedListRule}