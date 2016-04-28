/**
 * This module provides an api for handling content objects e.g. Posts, Polls...
 *
 * @type undefined|Function
 */

humhub.initModule('content', function(module, require, $) {
    var client = require('client');
    var object = require('util').object;
    var actions = require('actions');
    
    var Content = function(container) {
        if(!container) { //Create content
            return;
        }
        this.$ = (object.isString(container)) ? $('#' + container) : container;
        this.contentBase = this.$.data('content-base');
    };
    
    Content.prototype.getContentActions = function() {
        return ['create','edit','delete'];
    };
    
    Content.prototype.getKey = function () {
        return this.$.data('content-pk');
    };
    
    Content.prototype.data = function(dataSuffix) {
        var result = this.$.data(dataSuffix);
        if(!result) {
            var parentContent = this.getParentContentBase();
            if(parentContent) {
                return parentContent.data(dataSuffix);
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
        if(indexOf(this.getContentActions(), 'create') < 0) {
            return;
        }
        
        this.edit(addContentHandler);
    };
    
    Content.prototype.edit = function (successHandler) {
        if(indexOf(this.getContentActions(), 'edit') < 0) {
            return;
        }
        
        var editUrl = this.data('content-edit-url');
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
    
    Content.prototype.delete = function () {
        if(this.getContentActions().indexOf('delete') < 0) {
            return;
        }
        
        var that = this;
        var url = this.data('content-delete-url');
        if(url) {
             client.post(url, {
                 data: {
                     id: that.getKey()
                 },
                 success: function(json) {
                     json.success;
                     that.remove();
                 },
                 error: function(json) {
                     console.error(json);
                 }
             })
        } else {
            console.error('Content delete was called, but no url could be determined for '+this.contentBase);
        }
    };
    
    Content.prototype.replaceContent = function(content) {
        try {
            var that = this;
            this.$.animate({ opacity: 0 }, 'fast', function() {
                that.$.html($(content).children());
                that.$.stop().animate({ opacity: 1 }, 'fast');
                if(that.highlight) {
                    that.highlight();
                }
            });
        } catch(e) {
            console.error('Error occured while replacing content: '+this.$.attr('id') , e);
        }
    };
    
    Content.prototype.remove = function() {
        var that = this;
        this.$.animate({ height: 'toggle', opacity: 'toggle' }, 'fast', function() {
            that.$.remove();
            //TODO: fire global event
        });
    };
    
    Content.getContentBase = function($element) {
        return $element.closest('[data-content-base]');
    };
    
    Content.getInstance = function($contentBase) {
        $contentBase = (object.isString($contentBase)) ? $('#'+$contentBase) : $contentBase;
        var ContentType = require($contentBase.data('content-base'));
        if(ContentType) {
            return new ContentType($contentBase);
        }
    };
    
    var init = function() {
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
    handleAction = function(event) {
        var $contentBase = Content.getContentBase(event.$trigger);
        if($contentBase.length) {
            //Initialize a content instance by means of the content-base type and execute the handler
            var content = Content.getInstance($contentBase);
            if(content) {
                //Check if the content instance provides this actionhandler
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
    
    module.export({
        Content : Content,
        init : init,
        handleAction: handleAction
    });
});