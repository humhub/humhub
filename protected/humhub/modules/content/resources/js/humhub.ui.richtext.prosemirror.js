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
    const i18n = require('i18n');

    const MarkdownEditor = prosemirror.MarkdownEditor;
    const MentionProvider = prosemirror.MentionProvider;

    const RichTextEditor = Widget.extend();

    module.requiredI18nCategories = ['base', 'ContentModule.richtexteditor'];

    const getRichTextEditorTranslation = function (key) {
        const translations = {
            'Wrap in block quote': i18n.t('ContentModule.richtexteditor', 'Wrap in block quote'),
            'Wrap in bullet list': i18n.t('ContentModule.richtexteditor', 'Wrap in bullet list'),
            'Toggle code font': i18n.t('ContentModule.richtexteditor', 'Toggle code font'),
            'Switch editor mode': i18n.t('ContentModule.richtexteditor', 'Switch editor mode'),
            'Fullscreen': i18n.t('ContentModule.richtexteditor', 'Fullscreen'),
            'Change to code block': i18n.t('ContentModule.richtexteditor', 'Change to code block'),
            'Code': i18n.t('ContentModule.richtexteditor', 'Code'),
            'Toggle emphasis': i18n.t('ContentModule.richtexteditor', 'Toggle emphasis'),
            'Change to heading': i18n.t('ContentModule.richtexteditor', 'Change to heading'),
            'Insert horizontal rule': i18n.t('ContentModule.richtexteditor', 'Insert horizontal rule'),
            'Horizontal rule': i18n.t('ContentModule.richtexteditor', 'Horizontal rule'),
            'Insert image': i18n.t('ContentModule.richtexteditor', 'Insert image'),
            'Image': i18n.t('ContentModule.richtexteditor', 'Image'),
            'Location': i18n.t('ContentModule.richtexteditor', 'Location'),
            'Title': i18n.t('ContentModule.richtexteditor', 'Title'),
            'Text': i18n.t('ContentModule.richtexteditor', 'Text'),
            'OK': i18n.t('ContentModule.richtexteditor', 'OK'),
            'Cancel': i18n.t('base', 'Cancel'),
            'Suggestions': i18n.t('ContentModule.richtexteditor', 'Suggestions'),
            'No results': i18n.t('ContentModule.richtexteditor', 'No results'),
            'Required field': i18n.t('ContentModule.richtexteditor', 'Required field'),
            'Text Formatting': i18n.t('ContentModule.richtexteditor', 'Text Formatting'),
            'Width': i18n.t('ContentModule.richtexteditor', 'Width'),
            'Height': i18n.t('ContentModule.richtexteditor', 'Height'),
            'Add or remove link': i18n.t('ContentModule.richtexteditor', 'Add or remove link'),
            'Create a link': i18n.t('ContentModule.richtexteditor', 'Create a link'),
            'Link target': i18n.t('ContentModule.richtexteditor', 'Link target'),
            'Wrap in ordered list': i18n.t('ContentModule.richtexteditor', 'Wrap in ordered list'),
            'Change to paragraph': i18n.t('ContentModule.richtexteditor', 'Change to paragraph'),
            'Paragraph': i18n.t('ContentModule.richtexteditor', 'Paragraph'),
            'Toggle strikethrough': i18n.t('ContentModule.richtexteditor', 'Toggle strikethrough'),
            'Toggle strong style': i18n.t('ContentModule.richtexteditor', 'Toggle strong style'),
            'Create table': i18n.t('ContentModule.richtexteditor', 'Create table'),
            'Delete table': i18n.t('ContentModule.richtexteditor', 'Delete table'),
            'Table options': i18n.t('ContentModule.richtexteditor', 'Table options'),
            'Insert table': i18n.t('ContentModule.richtexteditor', 'Insert table'),
            'Rows': i18n.t('ContentModule.richtexteditor', 'Rows'),
            'Columns': i18n.t('ContentModule.richtexteditor', 'Columns'),
            'Insert column before': i18n.t('ContentModule.richtexteditor', 'Insert column before'),
            'Insert column after': i18n.t('ContentModule.richtexteditor', 'Insert column after'),
            'Delete column': i18n.t('ContentModule.richtexteditor', 'Delete column'),
            'Insert row before': i18n.t('ContentModule.richtexteditor', 'Insert row before'),
            'Insert row after': i18n.t('ContentModule.richtexteditor', 'Insert row after'),
            'Delete row': i18n.t('ContentModule.richtexteditor', 'Delete row'),
            'Upload and include a File': i18n.t('ContentModule.richtexteditor', 'Upload and include a File'),
            'Upload File': i18n.t('ContentModule.richtexteditor', 'Upload File'),
            'Insert': i18n.t('ContentModule.richtexteditor', 'Insert'),
            'Type': i18n.t('ContentModule.richtexteditor', 'Type'),
            // Emoji picker
            'Insert Emoji': i18n.t('ContentModule.richtexteditor', 'Insert Emoji'),
            'Categories': i18n.t('ContentModule.richtexteditor', 'Categories'),
            'Your browser does not support color emoji.': i18n.t('ContentModule.richtexteditor', 'Your browser does not support color emoji.'),
            'Frequently used': i18n.t('ContentModule.richtexteditor', 'Frequently used'),
            'Loading...': i18n.t('ContentModule.richtexteditor', 'Loading...'),
            'Could not load emoji.': i18n.t('ContentModule.richtexteditor', 'Could not load emoji.'),
            'Emoji picker': i18n.t('ContentModule.richtexteditor', 'Emoji picker'),
            'When search results are available, press up or down to select and enter to choose.': i18n.t('ContentModule.richtexteditor', 'When search results are available, press up or down to select and enter to choose.'),
            'Search': i18n.t('ContentModule.richtexteditor', 'Search'),
            'Search results': i18n.t('ContentModule.richtexteditor', 'Search results'),
            'When expanded, press up or down to select and enter to choose.': i18n.t('ContentModule.richtexteditor', 'When expanded, press up or down to select and enter to choose.'),
            // The picker replaces the placeholder itself
            'Choose a skin tone (currently {skinTone})': i18n.t('ContentModule.richtexteditor', 'Choose a skin tone (currently {skinTone})', {skinTone: '{skinTone}'}),
            'Skin tones': i18n.t('ContentModule.richtexteditor', 'Skin tones'),
            'Default': i18n.t('ContentModule.richtexteditor', 'Default'),
            'Light': i18n.t('ContentModule.richtexteditor', 'Light'),
            'Medium-Light': i18n.t('ContentModule.richtexteditor', 'Medium-Light'),
            'Medium': i18n.t('ContentModule.richtexteditor', 'Medium'),
            'Medium-Dark': i18n.t('ContentModule.richtexteditor', 'Medium-Dark'),
            'Dark': i18n.t('ContentModule.richtexteditor', 'Dark'),
            'Custom': i18n.t('ContentModule.richtexteditor', 'Custom'),
            'Smileys & Emotion': i18n.t('ContentModule.richtexteditor', 'Smileys & Emotion'),
            'People & Body': i18n.t('ContentModule.richtexteditor', 'People & Body'),
            'Animals & Nature': i18n.t('ContentModule.richtexteditor', 'Animals & Nature'),
            'Food & Drink': i18n.t('ContentModule.richtexteditor', 'Food & Drink'),
            'Travel & Places': i18n.t('ContentModule.richtexteditor', 'Travel & Places'),
            'Activities': i18n.t('ContentModule.richtexteditor', 'Activities'),
            'Objects': i18n.t('ContentModule.richtexteditor', 'Objects'),
            'Symbols': i18n.t('ContentModule.richtexteditor', 'Symbols'),
            'Flags': i18n.t('ContentModule.richtexteditor', 'Flags'),
            'Heading': i18n.t('ContentModule.richtexteditor', 'Heading'),
        };

        return translations[key] || key;
    };

    RichTextEditor.component = 'humhub-ui-richtexteditor';

    RichTextEditor.prototype.getDefaultOptions = function () {
        return {
            attributes: {
                'class': 'atwho-input form-control humhub-ui-richtext',
                'data-ui-markdown': true,
            },
            mention: {
                provider: new HumHubMentionProvider($.extend({}, module.config.mention, {
                    minInputText: i18n.t('base', 'Please type at least {count} characters', {count: 2})
                }))
            },
            link: {
                validate: module.config.validate
            },
            emoji: module.config.emoji,
            oembed: module.config.oembed,
            image: module.config.image,
            markdownEditorMode: module.config.markdownEditorMode,
            translate: function (key) {
                return getRichTextEditorTranslation(key);
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

        this.options.attributes = $.extend(this.getAccessibilityAttributes(), this.options.attributes);

        if (this.options.disabled) {
            setTimeout($.proxy(this.disable, this), 50);
        }

        // const options = $.extend({}, this.options, {exclude: ['blockquote', 'bullet_list', 'strong', 'code', 'code_block', 'em', 'image', 'list_item', 'ordered_list', 'heading', 'link', 'clipboard']});

        this.editor = new MarkdownEditor(this.$, this.options);
        this.editor.init(this.$.find('[data-ui-richtext]').text());

        const that = this;
        this.$.on('focusout', function () {
            that.getInput().val(that.editor.serialize()).trigger('blur');
        }).on('clear', function () {
            that.editor.clear();
            that.getInput().val('').trigger('blur');
            that.resetBackup();
        }).on('restoreBackup', function () {
            that.restoreBackup();
        }).on('discardBackup', function () {
            that.discardBackup();
        });

        if (this.options.backupInterval) {
            this.initBackup();
        }

        if (this.options.focus) {
            this.editor.view.focus();
        }

        if (this.options.markdownEditorMode) {
            this.editor.showSourceView();
        }
    };

    /**
     * The draft backup keeps an unsent text in the `sessionStorage` of the tab, so a reload or a click away does not
     * lose it. It is restored into the same form, on the same page, for the same user, and only onto the
     * text the editor opened with: once that text was saved (by anyone), the draft is outdated.
     * Submitting its form drops it, so does a deliberate discard: an emptied editor (`clear`), a cancel or a confirmed
     * "Unsaved changes" question (`discardBackup`).
     */
    RichTextEditor.prototype.initBackup = function () {
        // The same form on another page (another space's stream) or for another user is another draft
        this.backupKey = [require('user').guid(), window.location.pathname, this.getInput().attr('id')].join('|');
        this.restoreBackup();

        // A submitted text is saved, or comes back with the form's errors and is still in the editor
        this.$.closest('form')
            .on('submit', () => this.resetBackup())
            .on('click', '[type="submit"]', () => this.resetBackup());

        this.backupTimer = setInterval(() => {
            if (!this.$[0].isConnected) {
                // The form is gone (another page, a replaced form), its draft stays for it
                clearInterval(this.backupTimer);
                return;
            }
            this.backup();
        }, this.options.backupInterval * 1000);
    };

    /**
     * Takes the current text as the one the editor opened with and restores a draft typed onto it. Also called
     * through the `restoreBackup` event when the text is set after the editor booted (RichTextField.vue).
     */
    RichTextEditor.prototype.restoreBackup = function () {
        if (!this.backupKey) {
            return;
        }

        // Both as the editor writes them, so a text it rewrites (e.g. other list markers) still matches
        this.backupBase = this.editor.serialize();
        this.backupedValue = this.backupBase;

        const draft = this.getBackup()[this.backupKey];
        if (draft && typeof draft.text === 'string' && draft.base === this.backupBase && draft.text !== this.backupBase) {
            this.editor.init(draft.text);
            // The form can be submitted without the editor ever losing the focus, which updates the input
            this.getInput().val(draft.text);
            this.backupedValue = draft.text;
        }
    };

    RichTextEditor.prototype.getBackup = function () {
        try {
            const backup = JSON.parse(sessionStorage.getItem(this.options.backupCookieKey));
            return backup && typeof backup === 'object' ? backup : {};
        } catch (e) {
            // Site data blocked by the browser, or not ours
            return {};
        }
    };

    RichTextEditor.prototype.setBackup = function (backup) {
        try {
            if (Object.keys(backup).length) {
                sessionStorage.setItem(this.options.backupCookieKey, JSON.stringify(backup));
            } else {
                sessionStorage.removeItem(this.options.backupCookieKey);
            }
        } catch (e) {
            // Site data blocked by the browser, or the storage is full: no backup
        }
    };

    RichTextEditor.prototype.backup = function () {
        if (!this.backupKey) {
            return;
        }

        const text = this.editor.serialize();
        if (text === this.backupedValue) {
            return;
        }
        this.backupedValue = text;

        const backup = this.getBackup();
        if (text === '' || text === this.backupBase) {
            // Nothing the saved text does not have
            delete backup[this.backupKey];
        } else {
            backup[this.backupKey] = {text: text, base: this.backupBase};
        }
        this.setBackup(backup);
    };

    /**
     * Drops the draft of an emptied editor
     */
    RichTextEditor.prototype.resetBackup = function () {
        if (!this.backupKey) {
            return;
        }

        this.backupedValue = this.editor.serialize();
        const backup = this.getBackup();
        delete backup[this.backupKey];
        this.setBackup(backup);
    };

    /**
     * Drops the draft of a text the user discarded, and backs up no more: the form goes away
     */
    RichTextEditor.prototype.discardBackup = function () {
        this.resetBackup();
        clearInterval(this.backupTimer);
        this.backupKey = null;
    };

    RichTextEditor.prototype.focus = function () {
        this.editor.view.focus();
    };

    /**
     * The editor is a text box of several lines (WCAG 4.1.2), named by the label of its field, or else by its
     * placeholder, which is gone once something is typed
     *
     * @returns {Object} the attributes of the editable element
     */
    RichTextEditor.prototype.getAccessibilityAttributes = function () {
        const attributes = {role: 'textbox', 'aria-multiline': 'true'};
        const ids = [this.getInput().attr('id'), this.$.attr('id')].filter(Boolean);
        const label = $(ids.map(function (id) {
            return 'label[for="' + id + '"]';
        }).join(', ')).first();

        if (label.length) {
            if (!label.attr('id')) {
                label.attr('id', ids[0] + '-label');
            }
            attributes['aria-labelledby'] = label.attr('id');
        } else if (this.options.placeholder && this.options.placeholder.text) {
            attributes['aria-label'] = this.options.placeholder.text;
        }

        // The hint and the error message of the field describe its container or the hidden input, the editor is what
        // is read
        const describedBy = [this.$.attr('aria-describedby'), this.getInput().attr('aria-describedby'), this.getKeyboardHint().attr('id')]
            .join(' ').trim();
        attributes['aria-describedby'] = [...new Set(describedBy.split(/\s+/))].join(' ');

        return attributes;
    };

    /**
     * Tab indents in a list, so the editor says how to leave it (WCAG 2.1.2)
     *
     * @returns {jQuery} the visually hidden hint
     */
    RichTextEditor.prototype.getKeyboardHint = function () {
        const id = this.$.attr('id') + '-keyboard-hint';
        let $hint = this.$.children('#' + id);
        if (!$hint.length) {
            $hint = $('<span class="visually-hidden">').attr('id', id)
                .text(i18n.t('ContentModule.richtexteditor', 'In a list, Tab indents the item. Press Escape, then Tab, to leave the editor.'))
                .appendTo(this.$);
        }

        return $hint;
    };

    RichTextEditor.prototype.disable = function (tooltip) {
        tooltip = tooltip || this.options.disabledText;
        $(this.editor.view.dom).removeAttr('contenteditable').attr({
            disabled: 'disabled',
            'aria-disabled': 'true',
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
            this.$.attr('data-ui-richtext-rendered', '');
            additions.applyTo(this.$, {filter: ['highlightCode']});
            this.$.find('table').wrap('<div class="table-responsive"></div>');
            this.$.trigger('afterRender');
        }

        // See https://github.com/ProseMirror/prosemirror/issues/432
        document.execCommand('enableObjectResizing', false, 'false');
        document.execCommand('enableInlineTableEditing', false, 'false');
    };

    const HumHubMentionProvider = function (options) {
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
