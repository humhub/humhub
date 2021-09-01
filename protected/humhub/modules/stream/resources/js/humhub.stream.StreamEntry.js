/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * This module holds basic logic of stream entries.
 *
 * A stream entry usually holds a single content with an optional content component. This content component can be
 * retrieved by calling entry.contentComponent().
 *
 * The stream entry component provides basic stream features as a delete, reload, pin and  archive function.
 *
 */
humhub.module('stream.StreamEntry', function (module, require, $) {

    var util = require('util');
    var client = require('client');
    var contentModule = require('content');
    var Content = contentModule.Content;
    var Component = require('action').Component;
    var loader = require('ui.loader');
    var modal = require('ui.modal');
    var additions = require('ui.additions');
    var streamModule = require('stream');

    /**
     * Represents a single stream entry within a stream.
     *
     * @param {type} id
     * @returns {undefined}
     */
    var StreamEntry = Content.extend(function (id) {
        Content.call(this, id);
        // Set the stream so we have it even if the entry is detached.
        this.stream();
        var that = this;
        this.$.on('humhub:like:liked', function () {
            that.$.find('.turnOffNotifications').show();
            that.$.find('.turnOnNotifications').hide();
        });
    }, 'StreamEntry');

    StreamEntry.SELECTOR = '[data-stream-entry]';

    /**
     * Returns the surrounding stream component.
     */
    StreamEntry.prototype.stream = function () {
        return this.parent();
    };

    /**
     * Returns the included content component e.g. Poll/Post which is optional for an entry.
     *
     * @returns {undefined}
     */
    StreamEntry.prototype.contentComponent = function () {
        var children = Component.find(this.getContent(), '[data-content-component]', true);
        return children.length ? children[0] : undefined;
    };

    /**
     * Deletes this stream entry
     */
    StreamEntry.prototype.delete = function () {
        // Either call delete of a nestet content component or call default content delete
        var content = this.contentComponent();
        var promise = (content && content.delete) ? content.delete() : this.super('delete');

        var that = this;
        var stream = this.stream();

        promise.then(function ($confirm) {
            if ($confirm) {
                that.$.remove(); // Make sure to remove the wallentry node.
            }
        }).catch(function (err) {
            module.log.error(err, true);
        }).finally(function () {
            stream.onChange();
        });
    };

    /**
     * Reloads this stream entry
     */
    StreamEntry.prototype.reload = function () {
        if (typeof this.stream() !== 'undefined') {
            return this.stream().reloadEntry(this).catch(function (err) {
                module.log.error(err, true);
            });
        }
    };

    /**
     * Loads an inline edit form of this stream entry. Note this is only used for entries which supports inline edits.
     * @param evt
     */
    StreamEntry.prototype.edit = function (evt) {

        var that = this;

        that.loader();
        client.html(evt).then(function (response) {
            that.$.find('.stream-entry-edit-link').hide();
            that.$.find('.stream-entry-cancel-edit-link').show();
            that.setEditContent(response.html);
        }).catch(function (e) {
            module.log.error(e, true);
        }).finally(function () {
            that.loader(false);
        });
    };

    /**
     * Set thes the inline edit form content
     * @param content
     */
    StreamEntry.prototype.setEditContent = function (content) {
        this.replaceContent(content);
        this.$.find('.stream-entry-addons > .hideOnEdit').remove();
        this.apply();
        this.$.find('input[type="text"]:visible, textarea:visible, [contenteditable="true"]:visible').first().focus();
    };

    /**
     * Replaces the content part of this entry with the given html string.
     */
    StreamEntry.prototype.replaceContent = function (html) {
        var that = this;
        return new Promise(function (resolve, reject) {
            that.getContent().replaceWith(html);
            resolve(that);
        });
    };

    /**
     * Returns the content part of this entry.
     */
    StreamEntry.prototype.getContent = function () {
        return this.$.find('.content, .content_edit').first();
    };

    /**
     * Loads an edit form into the globalModal. Note this is only used for entries which supports modal based edits.
     * @param evt
     */
    StreamEntry.prototype.editModal = function (evt) {
        var that = this;
        modal.load(evt).then(function (response) {
            modal.global.$.one('hidden.bs.modal', function () {
                that.reload();
            });
        }).catch(function (e) {
            module.log.error(e, true);
        });
    };

    /**
     * Cancels the current edit form and reloads the actual entry
     */
    StreamEntry.prototype.cancelEdit = function () {
        var that = this;
        this.loader();
        this.reload().then(function () {
            that.$.find('.stream-entry-edit-link').show();
            that.$.find('.stream-entry-cancel-edit-link').hide();
        }).catch(function (err) {
            module.log.error(err, true);
        }).finally(function () {
            that.loader(false);
        });
    };

    /**
     * Applies ui additions for this entry
     */
    StreamEntry.prototype.apply = function () {
        additions.applyTo(this.$);
    };

    /**
     * Submits the current edit form.
     *
     * @param {type} evt
     * @returns {undefined}
     */
    StreamEntry.prototype.editSubmit = function (evt) {
        var that = this;
        client.submit(evt, {
            url: evt.url,
            dataType: 'html',
        }).status({
            200: function (response) {
                that.$.html(response.html);
                that.apply();
                that.highlight();
            },
            400: function (response) {
                that.replaceContent(response.html);
                that.apply();
            }
        }).catch(function (e) {
            module.log.error(e, true);
        });
    };

    /**
     * Highlights the entry content.
     */
    StreamEntry.prototype.highlight = function () {
        additions.highlight(this.getContent());
    };

    /**
     * Sets or unsets the entry loader.
     * @param $show
     */
    StreamEntry.prototype.loader = function ($show) {
        var $loader = this.$.find('.stream-entry-loader');
        if ($show === false) {
            loader.reset($loader);
            this.$.find('.wallentry-labels').show();
            this.$.find('.preferences').show();
            return;
        }

        this.$.find('.wallentry-labels').hide();
        this.$.find('.preferences').hide();
        loader.set($loader, {
            'position': 'left',
            'size': '8px',
            'css': {
                'padding': '0px',
                width: '60px'
            }
        });
    };

    /**
     * Update content record by action provided in action URL from attribute [data-action-url]
     * @param evt
     */
    var updateContentByActionUrl = function (evt) {
        this.loader();
        var that = this;
        client.post(evt).then(function (response) {
            if (response.success) {
                that.reload();
            } else {
                module.log.error(response, true);
                that.loader(false);
            }
        }).catch(function (e) {
            that.loader(false);
            module.log.error(e, true);
        });
    };

    /**
     * Changes the visibility (private/public) of this entry
     * @param evt
     */
    StreamEntry.prototype.toggleVisibility = updateContentByActionUrl;

    /**
     * Lock comments for the content
     * @param evt
     */
    StreamEntry.prototype.lockComments = updateContentByActionUrl;

    /**
     * Unlock comments for the content
     * @param evt
     */
    StreamEntry.prototype.unlockComments = updateContentByActionUrl;

    StreamEntry.prototype.isPinned = function (evt) {
        return this.$.is('[data-stream-pinned="1"]');
    };

    /**
     * Pins this entry to the top of the stream.
     * @param evt
     */
    StreamEntry.prototype.pin = function (evt) {
        var that = this;
        this.loader();
        var stream = this.stream();
        client.post(evt.url, evt).then(function (data) {
            if (data.success) {
                that.remove().then(function () {
                    stream.load({'contentId': that.getKey(), 'prepend': true});
                });
            } else if (data.info) {
                module.log.info(data.info, true);
            } else {
                module.log.error(data.error, true);
            }
        }, evt).catch(function (e) {
            module.log.error(e, true);
        }).finally(function () {
            that.loader(false);
        });
    };

    /**
     * Unpins this entry from the top of the stream.
     * @param evt
     */
    StreamEntry.prototype.unpin = function (evt) {
        var that = this;
        this.loader();
        client.post(evt.url).then(function () {
            that.stream().init();
        }).catch(function (e) {
            module.log.error(e, true);
            that.loader(false);
        });
    };


    /**
     * Replaces this entries dom element.
     *
     * @param newEntry
     * @returns {Promise}
     */
    StreamEntry.prototype.replace = function (newEntry) {
        var that = this;
        return new Promise(function (resolve, reject) {
            var $newEntry = $(newEntry);

            that.$.fadeOut(function () {
                that.$.replaceWith($newEntry);
                // Sinc the response does not only include the node itself we have to search it.
                that.$ = $newEntry.find(StreamEntry.SELECTOR)
                    .addBack(StreamEntry.SELECTOR);

                that.apply();

                that.$.hide().css('opacity', 1).fadeIn('fast', function () {
                    resolve();
                });
            });

        });
    };

    /**
     * Archives this entry.
     *
     * @param evt
     */
    StreamEntry.prototype.archive = function (evt) {
        var that = this;
        this.loader();
        client.post(evt.url).then(function (response) {
            if (response.success) {
                // Either just remove entry or reload it in case the stream includes archived entries
                if (typeof that.stream().filter === 'undefined' || that.stream().filter.isActive('entry_archived')) {
                    that.reload().then(function () {
                        streamModule.log.success('success.archive', true);
                    });
                } else {
                    that.remove().then(function () {
                        streamModule.log.success('success.archive', true);
                    });
                }
            } else {
                module.log.error(response, true);
            }
        }).catch(function (e) {
            module.log.error(e, true);
            that.loader(false);
        });
    };

    /**
     * Removes the archived flag from this entry.
     *
     * @param evt
     */
    StreamEntry.prototype.unarchive = function (evt) {
        var that = this;
        this.loader();
        client.post(evt.url).then(function (response) {
            if (response.success) {
                that.reload().then(function () {
                    streamModule.log.success('success.unarchive', true);
                }).catch(function (err) {
                    streamModule.log.error('error.default', true);
                });
            }
        }).catch(function (e) {
            module.log.error('Unexpected error', e, true);
            that.loader(false);
        });
    };

    /**
     * Removes this entry from the stream, but does not delete it.
     */
    StreamEntry.prototype.remove = function () {
        var stream = this.stream();
        return Content.prototype.remove.call(this).then($.proxy(stream.onChange, stream));
    };

    module.export = StreamEntry;
});
