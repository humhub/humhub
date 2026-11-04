/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

// Used as input to Rollup to generate the prosemirror js file

import * as state from "prosemirror-state";
import * as view from "prosemirror-view";
import * as transform from "prosemirror-transform";
import * as inputRules from "prosemirror-inputrules";
import * as commands from "prosemirror-commands";
import * as history from "prosemirror-history";
import * as keymap from "prosemirror-keymap";
import * as model from "prosemirror-model";
import * as pmmenu from "prosemirror-menu";
import {fixTables} from "prosemirror-tables";
import {Selection} from "prosemirror-state";

import * as menu from "./core/menu";
import * as prompt from "./core/prompt";
import * as loader from "./core/plugins/loader/plugin";

import {getParser, getSerializer, getRenderer} from "./markdown";
import {setupPlugins} from "./core/index";
import {$node} from "./core/util/node";
import {registerPreset, registerPlugin, buildPlugins} from "./core/plugins";
import * as markdown from "./markdown/index";

import MentionProvider from "./core/plugins/mention/provider";
import {EDIT_MODE_SOURCE, sourcePluginKey, isSourceMode, switchToSourceMode} from "./core/plugins/source/plugin";

import Context from './core/context';

$(document).on('mousedown.richtextProvider', function (evt) {
    if (!$(evt.target).closest('.humhub-richtext-provider:visible').length) {
        $('.humhub-richtext-provider:visible').each(function () {
            let $provider = $(this);

            let provider = $provider.data('provider');
            if (provider && provider.reset) {
                provider.reset();
            } else {
                $provider.hide().trigger('hidden');
            }
        });
    }
});

class BaseView {
    constructor(selector, options = {}) {
        this.$ = $(selector);

        let existingInstance = this.$.data('richtextInstance');
        if (existingInstance && existingInstance.view) {
            existingInstance.destroy();
            this.$.data('richtextInstance', this);
        }

        this.context = new Context(this, options);

        if (!this.isEdit()) {
            buildPlugins(this.context);
        }

        this.$.data('richtextInstance', this);
    }

    isEdit() {
        return this.context.options.edit;
    }

    trigger(trigger, args) {
        this.context.event.trigger(trigger, args);
        this.$.trigger(trigger, args);

        let callBack = 'on' + trigger.charAt(0).toUpperCase() + trigger.slice(1);

        if (typeof this.context.options[callBack] === 'function') {
            this.context.options[callBack].apply(this, args);
        }
    }

    on(event, handler) {
        this.$.on(event, handler);
    }

    getParser() {
        if (!this.parser) {
            this.parser = getParser(this.context);
        }

        return this.parser;
    }

    getRenderer() {
        if (!this.renderer) {
            this.renderer = getRenderer(this.context);
        }

        return this.renderer;
    }

    render(md) {
        md = md || this.$.text();
        return this.getRenderer().render(md, this);
    }
}

class MarkdownEditor extends BaseView {
    constructor(selector, options = {}) {
        if (typeof options.edit === 'undefined') {
            options.edit = true;
        }

        super(selector, options);
    }

    getSerializer() {
        if (!this.serializer) {
            this.serializer = getSerializer(this.context);
        }

        return this.serializer;
    }

    init(md = "") {
        if (this.view) {
            this.destroy();
        }

        this.trigger('beforeInit');

        let editorState = state.EditorState.create({
            doc: this.getParser().parse(md),
            plugins: setupPlugins(this.context)
        });

        let fix = fixTables(editorState);
        editorState = (fix) ? editorState.apply(fix.setMeta("addToHistory", false)) : editorState;

        this.view = new view.EditorView(this.$[0], {
            state: editorState
        });

        this.$editor = $(this.view.dom);

        // Dirty workaround, force inline menus to be removed, this is required e.g. if the editor is removed from dom
        $('.humhub-richtext-inline-menu').remove();

        this.trigger('init');
    }

    destroy() {
        // TODO: rather trigger event and handle in module
        if (this.context.$source) {
            this.context.$source.remove();
            this.context.$source = null;
        }

        this.trigger('beforeDestroy');
        this.view.destroy();
        this.trigger('afterDestroy');
    }

    isEdit() {
        return this.context.options.edit;
    }

    clear() {
        this.trigger('beforeClear');
        this.destroy();
        this.context.clear();
        this.$stage = null;
        this.init();
        this.trigger('afterClear');
    }

    getStage() {
        if (!this.$stage) {
            this.$stage = this.$.find('.ProseMirror');
        }
        return this.$stage;
    }

    isEmpty() {
        let doc = this.view.state.doc;
        return doc.childCount === 1 &&
            doc.firstChild.type.name === 'paragraph' &&
            doc.firstChild.content.size === 0 &&
            !this.context.hasContentDecorations()
    }

    focus(atEnd) {
        this.trigger('beforeFocus');

        // The extra condition is required when switching to source mode, but the state is not available yet
        if (isSourceMode(this.view.state) || (this.context.$source && this.context.$source.is(':visible'))) {
            if (atEnd) {
                let end = this.context.$source.val().length;
                this.context.$source[0].setSelectionRange(end, end);
            }
            return this.context.$source.focus();
        } else {
            if (atEnd) {
                const selection = Selection.atEnd(this.view.docView.node);
                const tr = this.view.state.tr.setSelection(selection);
                const state = this.view.state.apply(tr);
                this.view.updateState(state);
            }
            this.view.focus();
        }
        this.trigger('afterFocus');
    }

    hasFocus() {
        return isSourceMode(this.view.state)
            ? this.context.$source.is(':focus')
            : $(this.view.dom).is(':focus');
    }

    serialize() {
        this.trigger('beforeSerialize');

        let result = isSourceMode(this.view.state)
            ? this.context.$source.val()
            : this.getSerializer().serialize(this.view.state.doc);

        this.trigger('afterSerialize', result);

        return result;
    }

    showSourceView() {
        switchToSourceMode(this.context, false);
        this.view.dispatch(this.view.state.tr.setMeta(sourcePluginKey, EDIT_MODE_SOURCE));
    }

    transformToView() {
        this.destroy();
        let serialized = this.serialize();
        let view = new MarkdownView(this.$, this.context.options);
        view.init(serialized);
        return view;
    }
}

class MarkdownView extends BaseView {
    constructor(selector, options = {}) {
        options.edit = false;

        super(selector, options);

        this.renderer = getRenderer(this.context);
    }

    init(md) {
        this.context.source = md;
        this.$.html(this.render(md));
    }

    destroy() {
        this.$.html('');
    }

    clear() {
        this.destroy();
        this.context.clear();
        this.init();
    }

    isEdit() {
        return false;
    }

    transformToEditor(focus) {
        this.destroy();
        let editor = new MarkdownEditor(this.$, this.context.options);
        editor.init(this.context.source);

        if (focus !== false) {
            editor.focus();
        }

        return editor;
    }
}

window.humhub = window.humhub || {};

window.prosemirror = window.humhub.richtext = {
    MarkdownEditor: MarkdownEditor,
    MarkdownView: MarkdownView,
    globalOptions: {},
    state: state,
    view: view,
    transform: transform,
    inputRules: inputRules,
    model: model,
    commands: commands,
    history: history,
    keymap: keymap,
    menu: menu,
    loader: loader,
    pmmenu: pmmenu,
    prompt: prompt,
    getRenderer: getRenderer,
    plugin: {
        registerPreset: registerPreset,
        registerPlugin: registerPlugin,
        markdown: markdown
    },
    $node: $node,
    MentionProvider: MentionProvider
};

if (window.humhub && window.humhub.module) {
    window.humhub.module('ui.richtext', (module) => {
        module.export(window.humhub.richtext);
    });
}
