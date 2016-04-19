/**
 * This module provides an api for handling content objects e.g. Posts, Polls...
 *
 * @type undefined|Function
 */

humhub.initModule('content', function(module, require, $) {
    var client = require('client');
    var object = require('util').object;
    var actions = require('actions');
    
    module.init = function() {
        actions.registerHandler('humhub.modules.content.actiontHandler', function(event) {
            return module.handleAction(event);
        });
    };
    
    /**
     * Handles the given contentAction event. The event should provide the following properties:
     * 
     *  $trigger (required) : the trigger node of the event
     *  handler (required)  : the handler functionn name to be executed on the content
     *  type (optoinal)     : the event type 'click', 'change',...
     * 
     * @param {object} event - event object
     * @returns {Boolean} true if the contentAction could be executed else false
     */
    module.handleAction = function(event) {
        var $contentBase = this.getContentBase(event.$trigger);
        if($contentBase.length) {
            //Initialize a content instance by means of the content-base type and execute the handler
            var ContentType = require($contentBase.data('content-base'));
            if(ContentType) {
                var content = new ContentType($contentBase);
                if(event.handler && content[event.handler]) {
                    content[event.handler](event);
                    return true;
                }
            } else {
                console.error('No ContentType found for '+$contentBase.data('content-base'));
            }
        }
        return false;
    };
    
    module.getContentBase = function($element) {
        return $element.closest('[data-content-base]');
    };
    
    var Content = function(id) {
        if(!id) { //Create content
            return;
        }
        if (typeof id === 'string') {
            this.id = id;
            this.$ = $('#' + id);
        } else if (id.jquery) {
            this.$ = id;
            this.id = this.$.attr('id');
        }
    };
    
    Content.prototype.getKey = function () {
        return this.$.data('content-key');
    };
    
    Content.prototype.getEditUrl = function () {
        var result = this.$.data('content-edit-url');
        if(!result) {
            var parentContent = this.getParentContentBase('[data-content-base]');
            if(parentContent) {
                return parentContent.getEditUrl();
            }
        }
        return result;
    };
    
    Content.prototype.getParentContentBase = function() {
        var $parent = this.$.parent().closest('[data-content-base]');
        if($parent.length) {
            try {
                var ParentType = require($parent.data('content-base'));
                return new ParentType($parent);
            } catch(err) {
                console.error('Could not instantiate parent content base: '+$parent.data('content-base'));
            }
        }
    };
    
    Content.prototype.create = function (addContentHandler) {
        //Note that this Content won't have an id, so the backend will create an instance
        this.edit(addContentHandler);
    };
    
    Content.prototype.edit = function (successHandler) {
        var editUrl = this.getEditUrl();
        var contentId = this.getKey();
        var modal = require('ui.modal').global;
        
        if(!editUrl) {
            //Todo: handle error
            console.error('No editUrl found for edit content action editUrl: '+editUrl+ ' contentId '+contentId);
            return;
        }
   
        var that = this;
        
        client.ajax(editUrl, {
            data: {
                'id' : contentId
            },
            beforeSend: function() {
                modal.loader();
            },
            success: function(response) {
                //Successfully retrieved the edit form, now show it within a modal
                modal.content(response.getContent(), function() {
                    //Bind direct action handler we could use a global registeredHandler but this is more efficient
                    actions.bindAction(modal.getBody(), 'click', '[data-content-save]', function(event) {
                        client.submit(modal.getForm(), {
                            success : function(response) {
                                if(object.isFunction(successHandler)) {
                                    if(successHandler(response, modal)) {modal.close();};
                                } else {
                                    that.replaceContent(response.getContent());
                                    //TODO: check for content.highlight
                                    modal.close();
                                } 
                                event.finish();
                            },
                            error : function(error) {
                                //TODO: handle error
                                modal.error(error);
                                console.error('Error while submitting form :'+error);
                                event.finish();
                            }
                        });
                    });
                });
            },
            error: function(errResponse) {
                modal.error(errResponse);
                console.log('Error occured while editing content: '+errResponse.getFirstError());
                //Todo: handle error
            }
        });
    };
    
    Content.prototype.replaceContent = function(content) {
        try {
            this.$.html($(content).children());
        } catch(e) {
            console.error('Error occured while replacing content: '+this.id , e);
        }
    };
    
    Content.prototype.delete = function () {
        //Search for data-content-delte-url on root.
        //if(this.deleteModal) {open modal bla}
        //Call this url with data-content-pk
        //Trigger delete event
    };
    
    module.Content = Content;
});