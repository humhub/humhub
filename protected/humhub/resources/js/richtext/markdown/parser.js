/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {MarkdownParser} from "prosemirror-markdown";
import {getPlugins, PresetManager} from "../core/plugins";
import {getRenderer} from "./renderer";
import {getSchema} from "../core/schema";
import {
    DEFAULT_MEDIA_NODE_MAP,
    getBooleanAttrsFromTokenFlags,
    getMediaFlagsFromToken,
    getMediaNodeNameFromFlags,
    parseMediaOptionBlock,
    parseMediaOptionFlags
} from "./mediaOptions";

let presets = new PresetManager({
    name: 'parser',
    create: (context) => {
        return createParser(context);
    }
});

let getParser = (context) => {
    return presets.check(context);
};

let createParser = (context) => {
    const plugins = getPlugins(context);

    let tokens = {};
    plugins.forEach((plugin) => {
        if (!plugin.schema) {
            return;
        }

        const schemaSpecs = Object.assign({}, plugin.schema.nodes || {}, plugin.schema.marks || {});

        for (let key in schemaSpecs) {
            let spec = schemaSpecs[key];
            if (spec.parseMarkdown) {
                if (spec.parseMarkdown.block || spec.parseMarkdown.node || spec.parseMarkdown.mark || spec.parseMarkdown.ignore) {
                    tokens[key] = spec.parseMarkdown;
                } else {
                    let tokenKey = Object.keys(spec.parseMarkdown)[0];
                    tokens[tokenKey] = spec.parseMarkdown[tokenKey];
                }
            }
        }
    });

    return new MarkdownParser(context.schema || getSchema(context), getRenderer(context), tokens);
};

export {
    getParser,
    DEFAULT_MEDIA_NODE_MAP,
    parseMediaOptionFlags,
    parseMediaOptionBlock,
    getMediaFlagsFromToken,
    getMediaNodeNameFromFlags,
    getBooleanAttrsFromTokenFlags
};
