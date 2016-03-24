humhub.client = (function (module, $) {
    /**
     * Response Wrapper Object for
     * easily accessing common data
     */
    var Response = function (data) {
        this.data = data;
    };

    Response.prototype.isConfirmation = function () {
        return this.data && (this.data.status === 0);
    };

    Response.prototype.isError = function () {
        return this.data && this.data.status && (this.data.status > 0);
    };
    
    Response.prototype.getContent = function () {
        return this.$wrapperContent.children();
    };

    Response.prototype.getErrors = function () {
        return this.data.errors;
    };

    Response.prototype.getErrorCode = function () {
        return this.data.errorCode;
    };

    Response.prototype.toString = function () {
        return "{ status: " + this.data.status + " error: " + this.data.error + " data: " + this.data.data + " }";
    };

    var errorHandler = function (cfg, xhr, type, errorThrown, errorCode, path) {
        errorCode = (xhr) ? xhr.status : parseInt(errorCode);
        console.warn("AjaxError: " + type + " " + errorThrown + " - " + errorCode);

        if (cfg.error && object.isFunction(cfg.error)) {
            // "timeout", "error", "abort", "parsererror" or "application"
            //TODO: den trigger als this verwenden
            cfg.error(errorThrown, errorCode, type);
        } else {
            console.warn('Unhandled ajax error: ' + path + " type" + type + " error: " + errorThrown);
        }
    };

    var ajax = function (path, cfg) {
        var cfg = cfg || {};
        var async = cfg.async || true;
        var dataType = cfg.dataType || "json";
        
        var error = function (xhr, type, errorThrown, errorCode) {
            errorHandler(cfg, xhr, type, errorThrown, errorCode, path);
        };

        var success = function (json) {
            handleResponse(json, function(response) {
                if (response.isError()) { //Application errors
                    return error(undefined, "application", response.getError(), response.getErrorCode());
                } else if (cfg.success) {
                    cfg.success(response);
                }
            });
        };

        $.ajax({
            url: path,
            data : cfg.data,
            type: cfg.type,
            beforeSend:cfg.beforeSend,
            processData: cfg.processData,
            contentType: cfg.contentType,
            async: async,
            dataType: dataType,
            success: success,
            error: error 
        });
    };
    
    var handleResponse = function(json, callback) {
        var response = new Response(json);
        if(json.content) {
            response.$wrapperContent = $('<div id="respone-wrapper-container">'+json.content+'</div>');
            
            //Find all remote scripts and remove them from the partial
            var scriptSrcArr = [];
            response.$wrapperContent.find('script[src]').each(function() {
                scriptSrcArr.push($(this).attr('src'));
                $(this).remove();
            });
            
            //Load the remote scripts synchronously only if they are not already loaded.
            humhub.scripts.loadOnce(scriptSrcArr, true, function() {
                callback(response);
            }); 
        } else {
            callback(response);
        }
    };
    
    return {
        ajax : ajax
    };
})(humhub.client || {}, $);