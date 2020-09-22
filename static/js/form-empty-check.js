//Authr Mahmoud El-Badry
//Github: mahmoud-youssef
//Issue: https://github.com/humhub/humhub/issues/3771

var isSubmitting = false

$(document).ready(function () {
    
    $('form input').each(function(){
        if($(this).val() != '')
            isSubmitting = true;
    });

    $('form').data('initial-state', $('form').serialize());

    $(window).on('beforeunload', function() {
        if (!isSubmitting && $('form').serialize() != $('form').data('initial-state')){
            return '<div class="status-bar-body" style="top: 0px;"><div class="status-bar-content"><a class="status-bar-close pull-right">×</a><i class="fa fa-check-circle success"></i><span>You have unsaved changes which will not be saved.</span></div></div></div>';
        }
    });
});