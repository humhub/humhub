/**
 * Handle Image Upload
 */
$(document).on('humhub:ready', function () {
    'use strict';

    $('.fileupload').each(function () {


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
                progressall: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $('#profile-image-upload-bar .progress-bar').css('width', progress + '%');
                },
                done: function (e, data) {

                    if (data.result.files[0].error === true) {
                        handleUploadError(data.result);
                    } else {
                        if (profileImageUploaderUserGuid === profileImageUploaderCurrentUserGuid) {
                            $('#user-account-image').attr('src', data.result.files[0].url + '&c=' + Math.random());
                        }
                        $('#user-profile-image').attr('src', data.result.files[0].url + '&c=' + Math.random());
                        $('.user-' + profileImageUploaderUserGuid).attr('src', data.result.files[0].url + '&c=' + Math.random());
                        $('#user-profile-image').addClass('animated bounceIn');
                    }

                    $('#profile-image-upload-loader').addClass('d-none');
                    $('#profile-image-upload-bar .progress-bar').css('width', '0%');
                    $('#profile-image-upload-edit-button').removeClass('d-none');
                    $('#deleteLinkPost_modal_profileimagedelete').removeClass('d-none');


                }
            }).bind('fileuploadstart', function (e) {
                $('#profile-image-upload-loader').removeClass('d-none');
            }).bind('fileuploadstart', function (e) {
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
                progressall: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $('#banner-image-upload-bar .progress-bar').css('width', progress + '%');
                },
                done: function (e, data) {

                    if (data.result.files[0].error == true) {
                        handleUploadError(data.result);
                    } else {
                        $('#user-banner-image').attr('src', data.result.files[0].url + '&c=' + Math.random());
                        $('#user-banner-image').addClass('animated bounceIn');
                        $('#banner-image-upload-edit-button').removeClass('d-none');
                        $('#deleteLinkPost_modal_bannerimagedelete').removeClass('d-none');
                    }

                    $('#banner-image-upload-loader').addClass('d-none');
                    $('#banner-image-upload-bar .progress-bar').css('width', '0%');
                }
            }).bind('fileuploadstart', function (e) {
                $('#banner-image-upload-loader').removeClass('d-none');
            }).bind('fileuploadstart', function (e) {
                $('#user-banner-image').removeClass('animated bounceIn');
            })

        }


    });


});


/**
 * Handle upload errors for profile and banner images
 */
function handleUploadError(json) {

    $('#uploadErrorModal').appendTo(document.body);
    $('#uploadErrorModal .modal-dialog .modal-content .modal-body').html(json.files.errors.image);
    $('#uploadErrorModal').modal('show');

}

function resetProfileImage(json) {

    if (json.type == 'image') {
        $('#user-profile-image img').attr('src', json.defaultUrl);
        $('#user-profile-image').attr('src', json.defaultUrl);
        $('#deleteLinkPost_modal_profileimagedelete').addClass('d-none');
        $('#profile-image-upload-edit-button').addClass('d-none');
    } else if (json.type == "banner") {
        $('#user-banner-image').attr('src', json.defaultUrl);
        $('#deleteLinkPost_modal_bannerimagedelete').addClass('d-none');
        $('#banner-image-upload-edit-button').addClass('d-none');
    }

    $('.image-upload-buttons').addClass('d-none');
}

$(document).on('humhub:ready', function () {

    // override standard drag and drop behavior
    $(document).off('drop.humhub dragover.humhub').on('drop.humhub dragover.humhub', function (e) {
        e.preventDefault();
    });

    // show buttons at image rollover
    $('#profilefileupload').mouseover(function () {
        $('#profile-image-upload-buttons').removeClass('d-none');
    })

    // show buttons also at buttons rollover (better: prevent the mouseleave event)
    $('#profile-image-upload-buttons').mouseover(function () {
        $('#profile-image-upload-buttons').removeClass('d-none');
    });

    // hide buttons at image mouse leave
    $('#profilefileupload').mouseleave(function () {
        $('#profile-image-upload-buttons').addClass('d-none');
    })


    // show buttons at image rollover
    $('#bannerfileupload, .img-profile-data').mouseover(function () {
        $('#banner-image-upload-buttons').removeClass('d-none');
    })

    // show buttons also at buttons rollover (better: prevent the mouseleave event)
    $('#banner-image-upload-buttons').mouseover(function () {
        $('#banner-image-upload-buttons').removeClass('d-none');
    })

    // hide buttons at image mouse leave
    $('#bannerfileupload, .img-profile-data').mouseleave(function () {
        $('#banner-image-upload-buttons').addClass('d-none');
    })

});
