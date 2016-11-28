/**
 * This module provides an api for handling content objects e.g. Posts, Polls...
 *
 * @type undefined|Function
 */

humhub.module('content', function (module, require, $) {
    var client = require('client');
    var object = require('util').object;
    var actions = require('action');
    var Component = actions.Component;
    var event = require('event');
    var modal = require('ui.modal');

    var DATA_CONTENT_KEY = "content-key";
    var DATA_CONTENT_EDIT_URL = "content-edit-url";
    var DATA_CONTENT_SAVE_SELECTOR = "[data-content-save]";
    var DATA_CONTENT_DELETE_URL = "content-delete-url";


    var Content = function (container) {
        Component.call(this, container);
    };

    Content.getNodeByKey = function (key) {
        return $('[data-content-key="' + key + '"]');
    };

    object.inherits(Content, Component);

    Content.prototype.actions = function () {
        return ['create', 'edit', 'delete'];
    };

    Content.prototype.getKey = function () {
        return this.$.data(DATA_CONTENT_KEY);
    };

    //TODO: return promise
    Content.prototype.create = function (addContentHandler) {
        //Note that this Content won't have an id, so the backend will create an instance
        if (this.hasAction('create')) {
            return;
        }

        this.edit(addContentHandler);
    };

    //TODO: return promise
    Content.prototype.edit = function (successHandler) {
        /** 
         * 
         This is an old implementation currently there is no need for a default implementation
        
        
        if (!this.hasAction('edit')) {
            return;
        }

        var editUrl = this.data(DATA_CONTENT_EDIT_URL);
        var contentId = this.getKey();
        var modal = require('ui.modal').global;

        if (!editUrl) {
            //Todo: handle error
            console.error('No editUrl found for edit content action editUrl: ' + editUrl + ' contentId ' + contentId);
            return;
        }

        var that = this;

        client.ajax(editUrl, {
            data: {
                'id': contentId
            },
            beforeSend: function () {
                modal.loader();
            }
        }).then(function (response) {
            //Successfully retrieved the edit form, now show it within a modal
            modal.content(response.getContent(), function () {
                //Bind direct action handler we could use a global registeredHandler but this is more efficient
                actions.bindAction(modal.getBody(), 'click', DATA_CONTENT_SAVE_SELECTOR, function (event) {
                    client.submit(modal.getForm(), {
                        success: function (response) {
                            if (object.isFunction(successHandler)) {
                                if (successHandler(response, modal)) {
                                    modal.close();
                                }
                                ;
                            } else {
                                that.replaceContent(response.getContent());
                                //TODO: check for content.highlight
                                modal.close();
                            }
                            event.finish();
                        },
                        error: function (error) {
                            //TODO: handle error
                            modal.error(error);
                            console.error('Error while submitting form :' + error);
                            event.finish();
                        }
                    });
                });
            });
        }).catch(function (error) {
            modal.error(errResponse);
        });
        });
        */
    };

    //TODO: return promise
    Content.prototype.delete = function () {
        var that = this;
        return new Promise(function (resolve, reject) {
            if (!that.hasAction('delete')) {
                return;
            }

            modal.confirm().then(function ($confirmed) {
                if (!$confirmed) {
                    resolve(false);
                }

                that.loader();
                var deleteUrl = that.data(DATA_CONTENT_DELETE_URL);
                if (deleteUrl) {
                    client.post(deleteUrl, {
                        data: {
                            id: that.getKey()
                        }
                    }).then(function (response) {
                        that.remove().then(function () {
                            resolve(true)
                        });
                    }).catch(function (err) {
                        reject(err);
                    }).finally(function () {
                        that.loader(false);
                    });
                } else {
                    reject('Content delete was called, but no url could be determined for ' + that.base);
                    that.loader(false);
                }
            });
        });
    };

    /**
     * Abstract loader function which can be used to activate or deactivate a
     * loader within a content entry.
     * 
     * If $show is undefined or true the loader animation should be rendered
     * otherwise it should be remved.
     * 
     * @param {type} $show
     * @returns {undefined}
     */
    Content.prototype.loader = function ($show) {
        // Has to be overwritten by content type
    };

    Content.prototype.remove = function () {
        var that = this;
        return new Promise(function (resolve, reject) {
            that.$.animate({height: 'toggle', opacity: 'toggle'}, 'fast', function () {
                that.$.remove();
                event.trigger('humhub:modules:content:afterRemove', that);
                resolve(that);
            });
        });
    };
    
    Content.prototype.permalink = function (evt) {
        var permaLink = evt.$trigger.data('content-permalink');
        modal.global.set({
            header : module.text('modal.permalink.head'),
            body : '<textarea rows="3" class="form-control permalink-txt">' + permaLink + '</textarea><p class="help-block">'+module.text('modal.permalink.info')+'</p>',
            footer: '<a href="'+permaLink+'" data-modal-close class="btn btn-default">'+module.text('modal.permalink.close')+'</a><a href="'+permaLink+'" class="btn btn-primary" data-ui-loader>'+module.text('modal.permalink.open')+'</a>' 
        }).show();
        
        modal.global.$.find('textarea').focus().select();
        
        event.one('humhub:ready', function() {
            modal.global.close();
        });
    };

    module.export({
        Content: Content
    });
});