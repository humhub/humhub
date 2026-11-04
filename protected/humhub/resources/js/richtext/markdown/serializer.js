/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {MarkdownSerializer, MarkdownSerializerState} from "prosemirror-markdown";
import {getPlugins, PresetManager} from "../core/plugins";
import {buildMediaOptionBlock} from "./mediaOptions";

let presets = new PresetManager({
    name: 'serializer',
    create: (context) => {
        return createSerializer(context);
    }
});

const getSerializer = (context) => {
    return presets.check(context);
};

const createSerializer = (context) => {
    const plugins = getPlugins(context);
    let nodeSpec = {};
    let markSpec = {};
    plugins.forEach((plugin) => {
        if (!plugin.schema) {
            return;
        }

        let nodes = plugin.schema.nodes || {};

        for (let key in nodes) {
            let node = nodes[key];
            if (node.toMarkdown) {
                nodeSpec[key] = node.toMarkdown;
            }
        }

        let marks = plugin.schema.marks || {};

        for (let key in marks) {
            let mark = marks[key];
            if (mark.toMarkdown) {
                markSpec[key] = mark.toMarkdown;
            } else {
                markSpec[key] = {open: '', close: ''};
            }
        }
    });

    return new HumHubMarkdownSerializer(nodeSpec, markSpec);
};

class HumHubMarkdownSerializer extends MarkdownSerializer {
    // :: (Node, ?Object) → string
    // Serialize the content of the given node to
    // [CommonMark](http://commonmark.org/).
    serialize(content, options) {
        const state = new HumHubMarkdownSerializerState(
          this.nodes,
          this.marks,
          Object.assign(options || {}, {tightLists: false})
        );
        state.renderContent(content);
        return state.out;
    }
}

class HumHubMarkdownSerializerState extends MarkdownSerializerState {
    // :: (string, ?bool) → string
    // Escape the given string so that it can safely appear in Markdown
    // content. If `startOfLine` is true, also escape characters that
    // has special meaning only at the start of the line.
    esc(str, startOfLine) {
        // eslint-disable-next-line
        str = str.replace(/[|`*\\~\[\]]/g, "\\$&");
        if (startOfLine)
            str = str.replace(/^[:#\-*+]/, "\\$&").replace(/^(\d+)\./, "$1\\.");
        return str;
    }
}

export {getSerializer};
export {buildMediaOptionBlock};
