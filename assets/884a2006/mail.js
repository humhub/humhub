function loadMessage(messageId) {
    
    if ($("#mail_message_details").length) {
        
        // Inside message module
        $.ajax({
            'type': 'GET',
            'url': mail_loadMessageUrl.replace('-messageId-', messageId),
            'cache': false,
            'data': jQuery(this).parents("form").serialize(),
            'beforeSend': function() {
                $("#mail_message_details").html('<div class="loader"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></div></div></div>');
                $('.messagePreviewEntry').removeClass('selected');
                $(".messagePreviewEntry_"+messageId).addClass('selected');
            },
            'success': function(html) {
                $("#mail_message_details").html(html);
            }});
    } else {
        // Somewhere outside
        window.location.replace(mail_viewMessageUrl.replace('-messageId-', messageId));
    }
    

}
