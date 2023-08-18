/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 * @since 1.8
 */

humhub.module('ui.richtext.prosemirror', function (module, require, $) {
    const object = require('util').object;
    const client = require('client');
    const Widget = require('ui.widget').Widget;
    const additions = require('ui.additions');
    const event = require('event');

    const MarkdownEditor = prosemirror.MarkdownEditor;
    const MentionProvider = prosemirror.MentionProvider;

    const RichTextEditor = Widget.extend();

    RichTextEditor.component = 'humhub-ui-richtexteditor';

    RichTextEditor.prototype.getDefaultOptions = function () {
        return {
            attributes: {
                'class': 'atwho-input form-control humhub-ui-richtext',
                'data-ui-markdown': true,
            },
            mention: {
                provider: new HumHubMentionProvider(module.config.mention)
            },
            link: {
                validate: module.config.validate
            },
            emoji: module.config.emoji,
            oembed: module.config.oembed,
            markdownEditorMode: module.config.markdownEditorMode,
            translate: function (key) {
                return module.text(key);
            }
        };
    };

    RichTextEditor.prototype.init = function () {
        if (this.options.placeholder) {
            this.options.placeholder = {
                'text': this.options.placeholder,
                'class': 'placeholder atwho-placeholder'
            };
        }

        if (this.options.disabled) {
            setTimeout($.proxy(this.disable, this), 50);
        }

        // const options = $.extend({}, this.options, {exclude: ['blockquote', 'bullet_list', 'strong', 'code', 'code_block', 'em', 'image', 'list_item', 'ordered_list', 'heading', 'link', 'clipboard']});

        this.editor = new MarkdownEditor(this.$, this.options);
        this.editor.init(this.getInitValue());

        if (this.options.focus) {
            this.editor.view.focus();
        }

        const that = this;
        this.$.on('focusout', function () {
            that.getInput().val(that.editor.serialize()).trigger('blur');
        }).on('clear', function () {
            that.editor.clear();
        });

        if (this.options.backupInterval) {
            setInterval(() => this.backup(), this.options.backupInterval * 1000);
            event.on('humhub:content:afterSubmit', () => this.resetBackup());
        }

        if (this.options.markdownEditorMode) {
            this.editor.showSourceView();
        }
    };

    RichTextEditor.prototype.getInitValue = function () {
        const inputId = this.getInput().attr('id');
        const backup = this.getBackup();

        if (typeof backup[inputId] === 'string' && backup[inputId] !== '') {
            return backup[inputId];
        }

        return this.$.find('[data-ui-richtext]').text();
    }

    RichTextEditor.prototype.getBackup = function () {
        const backup = sessionStorage.getItem(this.options.backupCookieKey);

        if (typeof backup === 'string' && backup !== '') {
            return JSON.parse(backup);
        }

        return {};
    }

    RichTextEditor.prototype.backup = function (currentValue) {
        const inputId = this.getInput().attr('id');
        const isBackuped = typeof this.backupedValue !== 'undefined';

        if (typeof currentValue === 'undefined') {
            currentValue = this.editor.serialize();
        }

        if (!isBackuped && currentValue === '') {
            // Don't back up first empty value
            return;
        }

        if (isBackuped && currentValue === this.backupedValue) {
            // Don't back up same content twice
            return;
        }

        this.backupedValue = currentValue;

        const backup = this.getBackup();
        if (this.backupedValue === '' && typeof backup[inputId] !== 'undefined') {
            delete backup[inputId];
        } else {
            backup[inputId] = this.backupedValue;
        }

        if (Object.keys(backup).length) {
            sessionStorage.setItem(this.options.backupCookieKey, JSON.stringify(backup));
        } else {
            sessionStorage.removeItem(this.options.backupCookieKey);
        }
    };

    RichTextEditor.prototype.resetBackup = function () {
        this.backup('');
    }

    RichTextEditor.prototype.focus = function () {
        this.editor.view.focus();
    };

    RichTextEditor.prototype.disable = function (tooltip) {
        tooltip = tooltip || this.options.disabledText;
        $(this.editor.view.dom).removeAttr('contenteditable').attr({
            disabled: 'disabled',
            title: tooltip,
        }).tooltip({
            placement: 'bottom'
        });
    };

    RichTextEditor.prototype.getInput = function () {
        return $('#' + this.$.attr('id') + '_input');
    };

    const RichText = Widget.extend();

    RichText.component = 'humhub-ui-richtext';

    RichText.prototype.init = function () {
        // If in edit mode we do not actually render, we just hold the content
        if (!this.options.edit) {
            this.options.edit = false;
            this.editor = new MarkdownEditor(this.$, this.options);
            this.$.html(this.editor.render());
            additions.applyTo(this.$, {filter: ['highlightCode']});
            this.$.find('table').wrap('<div class="table-responsive"></div>');
            this.$.trigger('afterRender');
        }

        // See https://github.com/ProseMirror/prosemirror/issues/432
        document.execCommand('enableObjectResizing', false, 'false');
        document.execCommand('enableInlineTableEditing', false, 'false');
    };

    HumHubMentionProvider = function (options) {
        MentionProvider.call(this, options);
    };

    object.inherits(HumHubMentionProvider, MentionProvider);

    HumHubMentionProvider.prototype.find = function (query, node) {
        if (this.xhr) {
            this.xhr.abort();
        }

        const that = this;
        const $editor = Widget.closest(node);

        return new Promise(function (resolve, reject) {
            client.get($editor.options.mentioningUrl, {
                data: {keyword: query},
                beforeSend: function (jqXHR) {
                    that.xhr = jqXHR;
                }
            }).then(function (response) {
                resolve(response.data);
            }).catch(function () {
                reject(reject)
            });
        });

    };

    /**
     * Builds mentioning string from container link
     * @param $containerLink
     * @returns {string}
     */
    const buildMentioning = function ($containerLink) {
        const username = $containerLink.text();
        const guid = $containerLink.data('guid');
        const url = $containerLink.attr('href');
        return '[' + username + '](mention:' + guid + ' "' + url + '")';
    };

    module.export({
        initOnPjaxLoad: true,
        unload: function () {
            $('.humhub-richtext-provider').remove();
            $('.ProseMirror-prompt').remove();
        },
        RichTextEditor: RichTextEditor,
        RichText: RichText,
        buildMentioning: buildMentioning,
        api: prosemirror
    });
});
