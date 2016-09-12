
/**
 * Handle Image Upload
 */
$(function() {
    'use strict';
    
    $('.fileupload').each(function() {


        if ($(this).attr('id') == "profilefileupload") {

            /**
             * Handle Profile Image Upload
             */
            $(this).fileupload({
                dropZone: $(this),
                url: profileImageUploaderUrl,
                dataType: 'json',
                singleFileUploads: true,
                //formData: {'CSRF_TOKEN': csrfValue},
                limitMultiFileUploads: 1,
                progressall: function(e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $('#profile-image-upload-bar .progress-bar').css('width', progress + '%');
                },
                done: function(e, data) {

                    if (data.result.files.error == true) {
                        handleUploadError(data.result);
                    } else {
                        if (profileImageUploaderUserGuid === profileImageUploaderCurrentUserGuid) {
                            $('#user-account-image').attr('src', data.result.files.url + '&c=' + Math.random());
                        } 
                        $('#user-profile-image').attr('src', data.result.files.url + '&c=' + Math.random());
                        $('.user-' + profileImageUploaderUserGuid).attr('src', data.result.files.url + '&c=' + Math.random());
                        $('#user-profile-image').addClass('animated bounceIn');
                    }

                    $('#profile-image-upload-loader').hide();
                    $('#profile-image-upload-bar .progress-bar').css('width', '0%');
                    $('#profile-image-upload-edit-button').show();
                    $('#deleteLinkPost_modal_profileimagedelete').show();


                }
            }).bind('fileuploadstart', function(e) {
                $('#profile-image-upload-loader').show();
            }).bind('fileuploadstart', function(e) {
                $('#user-profile-image').removeClass('animated bounceIn');
            })

        } else if ($(this).attr('id') == "bannerfileupload") {

            /**
             * Handle Banner Image Upload
             */
            $(this).fileupload({
                dropZone: $(this),
                url: profileHeaderUploaderUrl,
                dataType: 'json',
                singleFileUploads: true,
                //formData: {'CSRF_TOKEN': csrfValue},
                limitMultiFileUploads: 1,
                progressall: function(e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $('#banner-image-upload-bar .progress-bar').css('width', progress + '%');
                },
                done: function(e, data) {

                    if (data.result.files.error == true) {
                        handleUploadError(data.result);
                    } else {
                        $('#user-banner-image').attr('src', data.result.files.url + '&c=' + Math.random());
                        $('#user-banner-image').addClass('animated bounceIn');
                        $('#banner-image-upload-edit-button').show();
                        $('#deleteLinkPost_modal_bannerimagedelete').show();
                    }

                    $('#banner-image-upload-loader').hide();
                    $('#banner-image-upload-bar .progress-bar').css('width', '0%');
                }
            }).bind('fileuploadstart', function(e) {
                $('#banner-image-upload-loader').show();
            }).bind('fileuploadstart', function(e) {
                $('#user-banner-image').removeClass('animated bounceIn');
            })

        }


    });


})


/**
 * Handle upload errors for profile and banner images
 */
function handleUploadError(json) {

    $('#uploadErrorModal').appendTo(document.body);
    $('#uploadErrorModal .modal-dialog .modal-content .modal-body').html(json.files.errors.image);
    $('#uploadErrorModal').modal('show');

}

function resetProfileImage(json) {

    if (json.type == 'profile') {
        $('#user-profile-image img').attr('src', json.defaultUrl);
        $('#user-profile-image').attr('src', json.defaultUrl);
        $('#deleteLinkPost_modal_profileimagedelete').hide();
        $('#profile-image-upload-edit-button').hide();
    } else if (json.type == "banner") {
        $('#user-banner-image').attr('src', json.defaultUrl);
        $('#deleteLinkPost_modal_bannerimagedelete').hide();
        $('#banner-image-upload-edit-button').hide();
    }

    $('.image-upload-buttons').hide();
}



$(document).ready(function() {

    // override standard drag and drop behavior
    $(document).bind('drop dragover', function(e) {
        e.preventDefault();
    });

    // show buttons at image rollover
    $('#profilefileupload').mouseover(function() {
        $('#profile-image-upload-buttons').show();
    })

    // show buttons also at buttons rollover (better: prevent the mouseleave event)
    $('#profile-image-upload-buttons').mouseover(function() {
        $('#profile-image-upload-buttons').show();
    });

    // hide buttons at image mouse leave
    $('#profilefileupload').mouseleave(function() {
        $('#profile-image-upload-buttons').hide();
    })


    // show buttons at image rollover
    $('#bannerfileupload, .img-profile-data').mouseover(function() {
        $('#banner-image-upload-buttons').show();
    })

    // show buttons also at buttons rollover (better: prevent the mouseleave event)
    $('#banner-image-upload-buttons').mouseover(function() {
        $('#banner-image-upload-buttons').show();
    })

    // hide buttons at image mouse leave
    $('#bannerfileupload, .img-profile-data').mouseleave(function() {
        $('#banner-image-upload-buttons').hide();
    })

});