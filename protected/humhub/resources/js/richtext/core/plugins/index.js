/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {inputRules, smartQuotes, emDash, ellipsis} from "prosemirror-inputrules";
import {keymap} from "prosemirror-keymap";
import doc from "./doc";
import blockquote from "./blockquote";
import bullet_list from "./bullet_list";
import code from "./code";
import code_block from "./code_block";
import em from "./em";
import emoji from "./emoji";
import hard_break from "./hard_break";
import heading from "./heading";
import historyPlugin from "./history";
import horizontal_rule from "./horizontal_rule";
import image from "./image";
import video from "./video";
import audio from "./audio";
import link from "./link";
import list_item from "./list_item";
import mention from "./mention";
import oembed from "./oembed";
import focus from "./focus";
import ordered_list from "./ordered_list";
import paragraph from "./paragraph";
import strikethrough from "./strikethrough";
import strong from "./strong";
import table from "./table";
import text from "./text";
import attributes from "./attributes";
import placeholder from "./placeholder";
import loader from "./loader";
import upload from "./upload";
import clipboard from "./clipboard";
import anchors from "./anchors";
import fullscreen from "./fullscreen";
import resizeNav from "./resize_nav";
import maxHeight from "./max_height";
import save from "./save";
import source from "./source";
import tabBehavior from "./tab_behavior";
import {PluginRegistry} from "./registry";
import file_handler from "./file_handler";

const registry = new PluginRegistry();

const registerPlugin = (plugin, options) => {
    registry.register(plugin, options);
};

const registerPreset = (id, plugins) => {
    registry.registerPreset(id, plugins);
};

registerPlugin(doc, 'markdown');
registerPlugin(historyPlugin, 'markdown');
registerPlugin(focus, 'markdown');
registerPlugin(clipboard, 'markdown');
registerPlugin(loader, 'markdown');
registerPlugin(paragraph, 'markdown');
registerPlugin(heading, 'markdown');
registerPlugin(blockquote, 'markdown');
registerPlugin(strikethrough, 'markdown');
registerPlugin(em, 'markdown');
registerPlugin(strong, 'markdown');
registerPlugin(code, 'markdown');
registerPlugin(link, 'markdown');
registerPlugin(code_block, 'markdown');
registerPlugin(hard_break, 'markdown');
registerPlugin(horizontal_rule, 'markdown');
registerPlugin(file_handler, 'markdown');
registerPlugin(image, 'markdown');
registerPlugin(video, 'markdown');
registerPlugin(audio, 'markdown');
registerPlugin(list_item, 'markdown');
registerPlugin(bullet_list, 'markdown');
registerPlugin(ordered_list, 'markdown');
registerPlugin(table, 'markdown');
registerPlugin(text, 'markdown');
registerPlugin(attributes, 'markdown');
registerPlugin(upload, 'markdown');
registerPlugin(placeholder, 'markdown');
registerPlugin(fullscreen, 'markdown');
registerPlugin(resizeNav, 'markdown');
registerPlugin(maxHeight, 'markdown');
registerPlugin(save, 'markdown');
registerPlugin(source, 'markdown');
registerPlugin(tabBehavior, 'markdown');
registerPlugin(emoji);
registerPlugin(mention);
registerPlugin(oembed);
registerPlugin(anchors);

registerPreset('normal', {
    extend: 'markdown',
    callback: (addToPreset) => {
        addToPreset('emoji', 'normal', {
            'before': 'hard_break'
        });

        addToPreset('mention', 'normal', {
            'before': 'ordered_list'
        });

        addToPreset('oembed', 'normal', {
            'before': 'ordered_list'
        });
    }
});

registerPreset('full', {
    extend: 'normal'
});

registerPreset('document', {
    extend: 'full',
    callback: (addToPreset) => {
        addToPreset('anchor', 'document', {
            'before': 'save'
        });
    }
});

class PresetManager {
    constructor(options) {
        this.map = {};
        this.options = options;
    }

    add(options, value) {
        this.map[options.preset] = value;
    }

    create(context) {
        return this.options.create.apply(null, [context]);
    }

    static isCustomPluginSet(options) {
        return !!options.exclude || !!options.include || !!options.only;
    }

    check(context) {
        let options = context.options;

        if (this.options.name && context[this.options.name]) {
            return context[this.options.name];
        }

        let result = [];

        if (!PresetManager.isCustomPluginSet(options) && this.map[options.preset]) {
            result = this.map[options.preset];
        }

        if (!result || (Array.isArray(result) && !result.length)) {
            result = this.create(context);

            if (!PresetManager.isCustomPluginSet(options)) {
                this.add(options, result);
            }
        }

        if (this.options.name) {
            context[this.options.name] = result;
        }

        return result;
    }
}

const getPlugins = (context) => {
    const options = context.options;

    if (context.plugins) {
        return context.plugins;
    }

    const presetMap = registry.getPresetRegistry(context);
    const toExtend = presetMap.get(options.preset) ? presetMap.get(options.preset) : registry.plugins;

    if (!PresetManager.isCustomPluginSet(options)) {
        return context.plugins = toExtend.slice();
    }

    let result = [];

    if (options.only) {
        options.only.forEach((pluginId) => {
            if (registry.pluginMap[pluginId]) {
                result.push(registry.pluginMap[pluginId]);
            } else {
                console.error('Could not include plugin ' + pluginId + ' plugin not registered!');
            }
        });

        return context.plugins = result;
    }

    if (options.exclude) {
        toExtend.forEach((plugin) => {
            if (plugin && !options.exclude.includes(plugin.id)) {
                result.push(plugin);
            }
        });
    } else {
        result = toExtend.slice();
    }

    if (options.include) {
        options.include.forEach((pluginId) => {
            if (registry.pluginMap[pluginId] && result.findIndex(plugin => plugin.id === pluginId) === -1) {
                result.push(registry.pluginMap[pluginId]);
            } else {
                console.error('Could not include plugin ' + pluginId + ' plugin not registered!');
            }
        });
    }

    return context.plugins = result;
};

const buildInputRules = (context) => {
    const plugins = context.plugins;
    const schema = context.schema;

    let rules = smartQuotes.concat([ellipsis, emDash]);
    plugins.forEach((plugin) => {
        if (plugin.inputRules) {
            rules = rules.concat(plugin.inputRules(schema));
        }
    });

    return inputRules({rules})
};

const buildPlugins = (context) => {
    const plugins = context.plugins;

    let result = [];
    plugins.forEach((plugin) => {
        const isEdit = context.editor.isEdit();
        if (plugin.init) {
            plugin.init(context, isEdit);
        }

        if (isEdit && plugin.plugins) {
            let pl = plugin.plugins(context);
            if (pl && pl.length) {
                result = result.concat(pl);
                context.prosemirrorPlugins[plugin.id] = pl;
            }
        }
    });

    return result;
};

const buildPluginKeymap = (context) => {
    const plugins = context.plugins;

    const result = [];
    plugins.forEach((plugin) => {
        if (plugin.keymap) {
            result.push(keymap(plugin.keymap(context)));
        }
    });

    return result;
};


// https://github.com/ProseMirror/prosemirror/issues/710
const isChromeWithSelectionBug = !!navigator.userAgent.match(/Chrome\/(5[89]|6[012])/);

export {
    isChromeWithSelectionBug,
    PresetManager,
    buildPlugins,
    buildPluginKeymap,
    buildInputRules,
    registerPlugin,
    registerPreset,
    getPlugins
}
