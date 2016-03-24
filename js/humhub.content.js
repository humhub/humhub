/**
 * This can should be used as parent class for all content implementations
 * @type undefined|Function
 */
humhub.modules.content = (function (module, $) {
    
    var Content = module.Content = function(id) {
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
        return this.$.data('content-edit-url');
    };
    
    Content.prototype.edit = function () {
        var modal = humhub.ui.modal;
        var editUrl = this.getEditUrl();
        var contentId = this.getKey();
        
        if(!editUrl || !contentId) {
            //Todo: handle error
            console.error('No editUrl or contentId found for edit content action editUrl: '+editUrl+ ' contentId '+contentId);
            return;
        }
   
        
        humhub.client.ajax(editUrl, {
            data: {
                'id' : contentId
            },
            beforeSend: function() {
                modal.showLoader();
                $('#globalModal').show();
            },
            success: function(response) {
                modal.content(response.getContent());
                //Parse Javascript
                //Show Modal
                //Todo: render edit modal from result
            },
            error: function(err) {
                console.log(err);
                //Todo: handle error
            }
        });
    };
    
    Content.prototype.delete = function () {
        //Search for data-content-delte-url on root.
        //if(this.deleteModal) {open modal bla}
        //Call this url with data-content-pk
        //Trigger delete event
    };
    
    return module;
})(humhub.modules || {}, $);