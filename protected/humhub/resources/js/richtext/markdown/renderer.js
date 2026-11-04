/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import markdownit from "markdown-it";
import {getPlugins, PresetManager} from "../core/plugins";

const presets = new PresetManager({
    name: 'renderer',
    create: (context) => {
        return createRenderer(context);
    }
});

const getRenderer = (context) => {
    return presets.check(context);
};

const createRenderer = (context) => {
    const markdownItOptions = context && context.options.markdownIt || {html: false, breaks: true, linkify: true};
    const renderer = markdownit(markdownItOptions);

    const plugins = getPlugins(context);
    plugins.forEach((plugin) => {
        if (plugin.registerMarkdownIt) {
            plugin.registerMarkdownIt(renderer);
        }
    });

    return renderer;
};

export {getRenderer};
