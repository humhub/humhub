function installUploader(uploaderId) {

    $('#fileUploaderButton_' + uploaderId).fileupload({
        dropZone: $(this),
        dataType: 'json',
        done: function(e, data) {
            $.each(data.result.files, function(index, file) {
                if (!file.error) {
                    hiddenValueField = $('#fileUploaderHiddenField_' + uploaderId);
                    hiddenValueField.val(hiddenValueField.val() + "," + file.guid);

                    $('#fileUploaderList_' + uploaderId).fadeIn('slow');
                    $('#fileUploaderListUl_' + uploaderId).append('<li style="padding-left: 24px;" class="mime ' + file.mimeIcon + '">' + file.name + '</li>');
                } else {
                    showFileUploadError(file);
                }
            });
        },
        progressall: function(e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#fileUploaderProgressbar_' + uploaderId).show();
            if (progress == 100) {
                $('#fileUploaderProgressbar_' + uploaderId).children().css('width', 100 + "%");
                $('#fileUploaderProgressbar_' + uploaderId).hide();
            } else {
                $('#fileUploaderProgressbar_' + uploaderId).children().css('width', progress + "%");
            }
        }
    }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');
}

function resetUploader(uploaderId) {
    $('#fileUploaderHiddenField_' + uploaderId).val('');
    $('#fileUploaderList_' + uploaderId).hide();
    $('#fileUploaderListUl_' + uploaderId).html('');
}

/**
 * Show File Upload Error
 * 
 * @param {type} file
 * @returns {undefined}
 */
function showFileUploadError(file) {

    // Parse errors array and extract error messages
    errorMessage = "";
    jQuery.each(file.errors, function() {
        jQuery.each(this, function() {
            errorMessage += this;
        });
    });

    $('#fileModal').remove();
    var alertMessage = '<div class="modal" id="fileModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"' +
            'aria-hidden="true">' +
            '<div class="modal-dialog modal-dialog-extra-small animated pulse">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
            '<h4 class="modal-title" id="myModalLabel">' + fileuploader_error_modal_title + '</h4> ' +
            '</div>' +
            '<div class="modal-body text-center">' + fileuploader_error_modal_errormsg + ' ' + file.name + '<br>' + errorMessage + '</div>' +
            '<div class="modal-footer">' +
            '<button type="button" class="btn btn-primary" data-dismiss="modal">' + fileuploader_error_modal_btn_close + '</button>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>'

    $('body').append(alertMessage);
    $('#fileModal').modal('show');

}


