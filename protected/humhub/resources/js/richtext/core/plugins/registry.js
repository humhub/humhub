export class PluginRegistry {
    constructor() {
        this.plugins = [];
        this.pluginMap = {};
        this.presets = new PresetRegistry(this);
        this.editorPresets = new PresetRegistry(this);
    }

    getPresetRegistry(context) {
        return context.editor.isEdit() ? this.editorPresets : this.presets;
    }

    register(plugin, options) {
        options = options || {};

        this.plugins.push(plugin);
        this.pluginMap[plugin.id] = plugin;

        options = (typeof options === 'string') ? {preset: options} : options;

        if (options.preset) {
            this.addToPreset(plugin, options.preset, options);
        }
    }

    registerPreset(id, plugins) {
        this.presets.register(id, plugins);
        this.editorPresets.register(id, plugins);

        if (plugins.callback) {
            plugins.callback($.proxy(this.addToPreset, this));
        }
    }

    addToPreset(plugin, presetId, options) {
        options = options || {};

        if (typeof plugin === 'string') {
            plugin = this.pluginMap[plugin];
        }

        if (!plugin) {
            console.warn('Could not add plugin to preset ' + presetId);
            return;
        }

        if (!plugin.renderOnly) {
            this.editorPresets.add(presetId, plugin, options);
        }

        if (!plugin.editorOnly) {
            this.presets.add(presetId, plugin, options);
        }
    }
}

export class PresetRegistry {
    constructor(pluginRegistry) {
        this.pluginRegistry = pluginRegistry;
        this.map = {};
    }

    get(presetId) {
        return this.map[presetId];
    }

    register(id, plugins) {
        let result = [];

        if (Array.isArray(plugins)) {
            plugins.forEach((pluginId) => {
                let plugin = this.pluginRegistry.pluginMap[pluginId];
                if (plugin) {
                    result.push(plugin);
                }
            })
        } else if (plugins.extend) {
            let toExtend = this.map[plugins.extend];

            if (!toExtend) {
                console.error('Could not extend richtext preset ' + plugins.extend + ' preset not registered!');
                return;
            }

            if (plugins.exclude && Array.isArray(plugins.exclude)) {
                toExtend.forEach((plugin) => {
                    if (plugin && !plugins.exclude.includes(plugin.id)) {
                        result.push(plugin);
                    }
                });
            } else {
                result = toExtend.slice(0);
            }

            if (plugins.include && Array.isArray(plugins.include)) {
                plugins.include.forEach((plugin) => {
                    if (!this.pluginRegistry.pluginMap[plugin]) {
                        console.error('Could not include plugin ' + plugin + ' to preset ' + id + ' plugin not found!');
                    } else {
                        result.push(this.pluginRegistry.pluginMap[plugin]);
                    }
                });
            }
        }

        this.map[id] = result;
    }

    add(presetId, plugin, options) {
        options = options || {};
        const preset = this.map[presetId] ? this.map[presetId].slice() : [];

        if (options['before'] && this.pluginRegistry.pluginMap[options['before']]) {
            let index = preset.indexOf(this.pluginRegistry.pluginMap[options['before']]);
            if (index >= 0) {
                preset.splice(index, 0, plugin);
            } else {
                console.warn('Tried appending plugin before non existing preset plugin: ' + presetId + ' before:' + options['before']);
                preset.push(plugin);
            }
        } else if (options['after'] && this.pluginRegistry.pluginMap[options['after']]) {
            let index = preset.indexOf(this.pluginRegistry.pluginMap[options['after']]);
            if (index >= 0) {
                preset.splice(index + 1, 0, plugin);
            } else {
                console.warn('Tried appending plugin after non existing preset plugin: ' + presetId + ' after:' + options['after']);
                preset.push(plugin);
            }
        } else {
            preset.push(plugin);
        }

        this.map[presetId] = preset;
    }
}
