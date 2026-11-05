/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {getPlugins} from "./plugins/index";
import {getSchema} from "./schema";

function uuidv4() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        const r = Math.random() * 16 | 0,
            v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}


export default class Context {
    constructor(editor, options) {
        this.event = $({});
        this.uuid = uuidv4();
        this.editor = editor;
        this.editor.context = this;
        this.id = this.editor.$.attr('id');
        this.init(options);

        // This is used to indicate active decorations relevant for some content related assumptions (e.g placeholder plugin)
        this.contentDecorations = [];

        // Map of related prosemirror plugin array by plugin id
        this.prosemirrorPlugins = {};
    }

    init(options) {
        if (options.pluginOptions) {
            $.extend(options, options.pluginOptions);
        }

        this.options = options;
        this.options.preset = options.preset || 'full';

        if (Array.isArray(this.options.exclude) && !this.options.exclude.length) {
            this.options.exclude = undefined;
        }

        if (typeof this.options.exclude === 'string') {
            this.options.exclude = [this.options.exclude];
        }

        if (Array.isArray(this.options.include) && !this.options.include.length) {
            this.options.include = undefined;
        }

        if (typeof this.options.include === 'string') {
            this.options.include = [this.options.include];
        }

        if (!Array.isArray(this.options.only) || !this.options.only.length) {
            this.options.only = undefined;
        }

        getPlugins(this);
        getSchema(this);
    }

    clear() {
        this.event.trigger('clear');
    }

    getGlobalOption(id, option, defaultValue) {
        let globalOptions = this.getGlobalOptions();

        if (option && typeof globalOptions[id] === 'undefined') {
            return defaultValue;
        }

        if (!option) {
            return globalOptions[id];
        }

        if (typeof globalOptions[id][option] === 'undefined') {
            return defaultValue;
        }

        return globalOptions[id][option];
    }

    getPresetOption(id, option, defaultValue) {
        let globalOptions = this.getGlobalOptions();

        if (!globalOptions.presets) {
            return defaultValue;
        }

        if (!globalOptions.presets[this.options.preset]
            || !globalOptions.presets[this.options.preset][id]) {
            return defaultValue;
        }

        if (!option) {
            return globalOptions.presets[this.options.preset][id];
        }

        if (typeof globalOptions.presets[this.options.preset][id][option] === 'undefined') {
            return defaultValue;
        }

        return globalOptions.presets[this.options.preset][id][option];
    }

    getOption(id, option, defaultValue) {
        // First try fetching option from context
        let result = this.getPluginOption(id, option);

        // Then check for global option for current preset
        if (!result) {
            result = this.getPresetOption(id, option);
        }

        // Then check for global option
        if (!result) {
            result = this.getGlobalOption(id, option);
        }

        return typeof result !== 'undefined' ? result : defaultValue;
    }


    getPluginOption(id, option, defaultValue) {
        let pluginOptions = this.options[id];

        if (!option) {
            return pluginOptions;
        } else if (pluginOptions) {
            return !(typeof pluginOptions[option] === 'undefined') ? pluginOptions[option] : defaultValue;
        }

        return defaultValue;
    }

    translate(key) {
        let translateOption = this.options.translate || this.getGlobalOptions().translate;

        if (!translateOption) {
            return key;
        }

        if (typeof translateOption === 'function') {
            return translateOption(key) || key;
        }

        if (typeof translateOption === 'object') {
            return translateOption[key] || key;
        }

        return key;
    }

    getGlobalOptions() {
        if (!window.humhub.richtext.globalOptions) {
            window.humhub.richtext.globalOptions = {};
        }

        return window.humhub.richtext.globalOptions;
    }

    getProsemirrorPlugins(id, prosemirror) {
        return this.prosemirrorPlugins[id];
    }

    getPlugin(id) {
        for (let i = 0; i < this.plugins.length; i++) {
            let plugin = this.plugins[i];
            if (plugin.id === id) {
                return plugin;
            }
        }
    }

    addContentDecoration(id) {
        if(this.contentDecorations.indexOf(id) < 0) {
            this.contentDecorations.push(id);
        }
    }

    removeContentDecoration(id) {
        let index = this.contentDecorations.indexOf();
        if (index >= 0) {
            this.contentDecorations.splice(index, 1);
        }
    }

    hasContentDecorations() {
        return !!this.contentDecorations.length;
    }
}
