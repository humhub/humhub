function resetLogoImage(json) {
    $('#deleteLinkPost_modal_logoimagedelete').hide();
    $('#logo-image').attr('src', '');
    $('#img-logo').hide();
    $('#text-logo').show();
}

$(document).ready(function () {

    // override standard drag and drop behavior
    $(document).bind('drop dragover', function (e) {
        e.preventDefault();
    });

    $("#logo").change(function () {
        readURL(this);
    });
});

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#logo-image').attr('src', e.target.result);
        }

        reader.readAsDataURL(input.files[0]);
    }
}
    
