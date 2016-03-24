humhub.ui.modal = (function (module, $) {
    var Modal = function() {};
    
    Modal.prototype.getModal = function() {
        if(!this.$global) {
            this.$global = $('#globalModal');
            this.initModal();
        }
        return this.$global;
    };
    
    Modal.prototype.initModal = function() {
        var that = this;
        this.reset();
        this.$global.on('click', '[data-modal-close]', function() {
            that.close();
        });
    };
    
    Modal.prototype.close = function() {
         this.$global.hide();
         this.$global.html('');
         this.reset();
    };
    
    Modal.prototype.reset = function() {
        this.content('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="loader"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></div></div></div>');
    };
    
    Modal.prototype.showLoader = function() {
        $(".modal-footer .btn").hide();
        $(".modal-footer .loader").removeClass("hidden");
        this.getModal().show();
    };
    
    Modal.prototype.content = function(content) {
        try {
            var that = this;
            console.log('add content modal');
            this.getModal().html(content).promise().always(function() {
                console.log('modal content added');
                humhub.additions.applyTo(that.getModal());
            });
        } catch(err) {
            console.error('Error while setting modal content', err);
            //We try to apply additions anyway
            humhub.additions.applyTo(that.getModal());
        }
    };
    
    return new Modal();
})(humhub.ui.modal || {}, $);