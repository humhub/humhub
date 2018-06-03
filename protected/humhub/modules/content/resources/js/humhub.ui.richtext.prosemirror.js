/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
humhub.module('ui.richtext.prosemirror', function(module, require, $) {
    
    var object = require('util').object;
    var client = require('client');
    var Widget = require('ui.widget').Widget;

    var MarkdownEditor = prosemirror.MarkdownEditor;
    var MentionProvider = prosemirror.MentionProvider;

    var RichTextEditor = function(node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(RichTextEditor, Widget);

    RichTextEditor.component = 'humhub-ui-richtexteditor';

    RichTextEditor.prototype.getDefaultOptions = function() {
        return {
            attributes:  {
                'class': 'atwho-input form-control humhub-ui-richtext',
                'data-ui-markdown': true,
            },
            mention: {
                provider: new HumHubMentionProvider(module.config.mention)
            },
            emoji: module.config.emoji,
            oembed: module.config.oembed,
            translate: function(key) {
                return module.text(key);
            }
        };
    };

    RichTextEditor.prototype.init = function() {
        if(this.options.placeholder) {
            this.options.placeholder = {
                text: this.options.placeholder,
                'class' : 'placeholder atwho-placeholder'
            };
        }

        if(this.options.disabled) {
            setTimeout($.proxy(this.disable, this), 50);
        }

        this.editor = new MarkdownEditor(this.$, this.options);
        $content = this.$.find('[data-ui-richtext]').text();
        this.editor.init($content);

        if(this.options.focus) {
            this.editor.view.focus();
        }

        var that = this;
        this.$.on('focusout', function() {
            that.getInput().val(that.editor.serialize());
        }).on('clear', function() {
            that.editor.clear();
        }).on('focus', function() {
            that.focus();
        });
    };

    RichTextEditor.prototype.focus = function(tooltip) {
        this.editor.view.focus();
        this.editor.view.focus();
    };

    RichTextEditor.prototype.disable = function(tooltip) {
        tooltip = tooltip || this.options.disabledText;
        debugger;
        $(this.editor.view.dom).removeAttr('contenteditable').attr({
            disabled: 'disabled',
            title: tooltip,
        }).tooltip({
            placement: 'bottom'
        });
    };

    RichTextEditor.prototype.getInput = function() {
        return $('#'+this.$.attr('id')+'_input');

    };

    var RichText = function(node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(RichText, Widget);

    RichText.component = 'humhub-ui-richtext';

    RichText.prototype.init = function() {
        // If in edit mode we do not actually render, we just hold the content
        if(!this.options.edit) {
            this.editor = new MarkdownEditor(this.$, this.options);
            this.$.html(this.editor.render());
        }
    };

    HumHubMentionProvider = function(options) {
        MentionProvider.call(this, options);
    };

    object.inherits(HumHubMentionProvider, MentionProvider);

    HumHubMentionProvider.prototype.find = function(query, node) {
        if(this.xhr) {
            this.xhr.abort();
        }

        var that = this;
        var $editor = Widget.closest(node);

        return new Promise(function(resolve, reject) {
            client.get($editor.options.mentioningUrl, {
                data: {keyword: query},
                beforeSend: function(jqXHR) {
                    that.xhr = jqXHR;
                }
            }).then(function(response) {
                resolve(response.data);
            }).catch(function(err) {
                reject(reject)
            });
        });

    };

    module.export({
        initOnPjaxLoad: true,
        unload: function(pjax) {
            $('.humhub-richtext-provider').remove();
        },
        RichTextEditor: RichTextEditor,
        RichText: RichText,
        api: prosemirror
    });
});