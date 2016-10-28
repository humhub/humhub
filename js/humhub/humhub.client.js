/**
 * Manages the client/server communication. Handles humhub json api responses and
 * pjax requests.
 */
humhub.initModule('client', function (module, require, $) {
    var object = require('util').object;

    /**
     * Response Wrapper Object for easily accessing common data
     */
    var Response = function (data, dataType) {
        if(!dataType || dataType === 'json') {        
            $.extend(this, data);
        } else if(dataType) {
            this[dataType] = data;
        } else {
            this.data = data;
        }
    };

    /**
     * Checks if the response is a confirmation of the request
     * @returns {Boolean}
     */
    Response.prototype.isConfirmation = function () {
        return this.getStatus() === 0;
    };

    //TODO: isValidationError status 2 

    /**
     * Checks if the response is marke
     * @returns {humhub.client_L5.Response.data.status|Boolean}
     */
    Response.prototype.isError = function () {
        return this.getStatus() > 0 || this.getErrors().length;
    };

    Response.prototype.getStatus = function () {
        return (this.status) ? this.status : -1;
    };

    Response.prototype.getFirstError = function () {
        var errors = this.getErrors();
        if (errors.length) {
            return errors[0];
        }
    };

    Response.prototype.setAjaxError = function (xhr, errorThrown, textStatus, data, status) {
        this.xhr = xhr;
        this.textStatus = textStatus;
        this.status = status || xhr.status;
        this.errors = [errorThrown];
    };

    /**
     * Returns an array of errors or an empty array so getErrors().length is always
     * safe.
     * @returns {array} error array or empty array
     */
    Response.prototype.getErrors = function () {
        var errors = this.errors || [];
        return (object.isString(errors)) ? [errors] : errors;
    };

    Response.prototype.toString = function () {
        return "{ status: " + this.getStatus() + " error: " + this.getErrors() + " data: " + this.getContent() + " }";
    };

    var submit = function ($form, cfg) {
        var cfg = cfg || {};
        $form = object.isString($form) ? $($form) : $form;
        cfg.type = $form.attr('method') || 'post';
        cfg.data = $form.serialize();
        var url = cfg['url'] || $form.attr('action');
        return ajax(url, cfg);
    };

    var post = function (path, cfg) {
        var cfg = cfg || {};
        cfg.type = 'POST';
        cfg.method = 'POST';
        return ajax(path, cfg);
    };
    
    var get = function (path, cfg) {
        var cfg = cfg || {};
        cfg.type = 'GET';
        cfg.method = 'GET';
        return ajax(path, cfg);
    };

    var ajax = function (path, cfg) {
        if(object.isFunction(cfg)) {
            cfg = {'success' : cfg};
        }
        
        var promise = new Promise(function (resolve, reject) {
            cfg = cfg || {};

            //Wrap the actual error handler with our own and call 
            var errorHandler = cfg.error;
            var error = function (xhr, textStatus, errorThrown, data) {
                //Textstatus = "timeout", "error", "abort", "parsererror", "application"
                if (errorHandler && object.isFunction(errorHandler)) {
                    var response = new Response();
                    response.setAjaxError(xhr, errorThrown, textStatus, data, xhr.status);
                    errorHandler(response);
                }
                reject({'textStatus': textStatus, 'response': xhr.responseJSON, 'error': errorThrown, 'data': data, 'status': xhr.status});
            };

            var successHandler = cfg.success;
            var success = function (data, textStatus, xhr) {
                var response = new Response(data, cfg.dataType);
                if (response.isError()) { //Application errors
                    return error(xhr, "application", response.getErrors(), data, response.getStatus());
                } else if (successHandler) {
                    response.textStatus = textStatus;
                    response.xhr = xhr;
                    successHandler(response);
                }
                
                resolve(response);
                
                promise.then(function() {
                    // If content with <link> tags are inserted in resolve, the ajaxComplete handler in yii.js
                    // makes sure redundant stylesheets are removed. Here we get sure it is called after inserting the response.
                    $(document).trigger('ajaxComplete');
                });
                
            };

            //Overwriting the handler with our wrapper handler
            cfg.success = success;
            cfg.error = error;
            cfg.url = path;

            //Setting some default values
            cfg.dataType = cfg.dataType || "json";

            $.ajax(cfg);
        });
        
        return promise;
    };

    module.export({
        ajax: ajax,
        post: post,
        get: get,
        submit: submit
    });
});

/**
 * 
 var handleResponse = function (json, callback) {
 var response = new Response(json);
 if (json.content) {
 response.$content = $('<div>' + json.content + '</div>');
 
 //Find all remote scripts and remove them from the partial
 var scriptSrcArr = [];
 response.$content.find('script[src]').each(function () {
 scriptSrcArr.push($(this).attr('src'));
 $(this).remove();
 });
 
 //Load the remote scripts synchronously only if they are not already loaded.
 scripts.loadOnceSync(scriptSrcArr, function () {
 callback(response);
 });
 } else {
 callback(response);
 }
 };
 */