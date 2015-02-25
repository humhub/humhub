<?php
/** @var $space Space */
?>

<div class="panel panel-default space-info" id="space-info-panel">


    <!-- Display panel menu widget -->
    <?php $this->widget('application.widgets.PanelMenuWidget', array('id' => 'space-info-panel')); ?>

    <div class="panel-heading"><?php echo Yii::t('SpaceModule.widgets_views_spaceInfo', '<strong>Space</strong> info'); ?></div>

    <div class="panel-body">
        <div class="media-body">
            <div class="media">
                <div class="image-upload-container" style="width: 80px; height: 80px; float: left; margin-right: 10px;">

                    <!-- profile image output-->
                    <img class="img-rounded " id="space-profile-image"
                         src="<?php echo $space->getProfileImage()->getUrl(); ?>"
                         data-src="holder.js/80x80" alt="80x80" style="width: 80px; height: 80px;"/>

                    <!-- check if the current user has admin rights for this space -->
                    <?php if ($space->isAdmin(Yii::app()->user->id)) : ?>
                        <form class="fileupload" id="spaceimageupload" action="" method="POST" enctype="multipart/form-data"
                              style="position: absolute; top: 0; left: 0; opacity: 0; height: 80px; width: 80px;">
                            <input type="file" name="spacefiles[]">
                        </form>

                        <div class="image-upload-loader" id="space-image-upload-loader" style="padding-top: 35px;">
                            <div class="progress image-upload-progess-bar" id="space-image-upload-bar">
                                <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="00"
                                     aria-valuemin="0"
                                     aria-valuemax="100" style="width: 0%;">
                                </div>
                            </div>
                        </div>

                        <div class="image-upload-buttons" id="space-image-upload-buttons">
                            <a herf="#" onclick="javascript:$('#spaceimageupload input').click();" class="btn btn-info btn-sm"><i
                                    class="fa fa-cloud-upload"></i></a>
                            <a id="profile-image-upload-edit-button"
                               style="<?php if (!$space->getProfileImage()->hasImage()) { echo 'display: none;'; } ?>"
                               href="<?php echo Yii::app()->createAbsoluteUrl('//space/admin/cropImage', array('guid' => $space->guid)); ?>"
                               class="btn btn-info btn-sm" data-toggle="modal" data-target="#globalModal"><i
                                    class="fa fa-edit"></i></a>
                        </div>
                    <?php endif; ?>

                </div>
                <strong><?php echo CHtml::encode($space->name); ?></strong>

                <div class="media-body" id="space-description"
                     style="overflow: hidden; max-height: 55px; font-size: 13px;">
                    <?php echo CHtml::encode($space->description); ?>
                </div>
                <a class="btn btn-default btn-xs pull-right hidden" id="more-button" style="margin-top: 5px;"
                   href="javascript:showMoreInfo();"><i class="fa fa-arrow-down"></i> <?php echo Yii::t('SpaceModule.widgets_views_spaceInfo', 'more'); ?></a>
            </div>
        </div>
    </div>
</div>

<!-- start: Error modal -->
<div class="modal" id="uploadErrorModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-extra-small animated pulse">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"
                    id="myModalLabel"><?php echo Yii::t('SpaceModule.widgets_views_spaceInfo', '<strong>Something</strong> went wrong'); ?></h4>
            </div>
            <div class="modal-body text-center">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary"
                        data-dismiss="modal"><?php echo Yii::t('SpaceModule.widgets_views_spaceInfo', 'Ok'); ?></button>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">


    $(document).ready(function () {

        // save the count of characters
        var _words = '<?php echo strlen($space->description); ?>';


        if (_words > 60) {
            // show more-button
            $('#more-button').removeClass('hidden');
        }
    });

    // current button state
    var _state = "more";

    function showMoreInfo() {

        if (_state == "more") {
            $('#space-description').css('max-height', '2000px');
            $('#more-button').html('<i class="fa fa-arrow-up"></i> <?php echo Yii::t('SpaceModule.widgets_views_spaceInfo', 'less'); ?>');
            _state = "less"
        } else {
            $('#space-description').css('max-height', '55px');
            $('#more-button').html('<i class="fa fa-arrow-down"></i> <?php echo Yii::t('SpaceModule.widgets_views_spaceInfo', 'more'); ?>');
            _state = "more"
        }

    }


    /**
     * Handle Image Upload
     */
    $(function () {
        'use strict';
        // Change this to the location of your server-side upload handler:
        var profileImageUrl = '<?php echo Yii::app()->createUrl('//space/admin/imageUpload', array('guid' => $space->guid)); ?>';

        $('.fileupload').each(function () {

            /**
             * Handle Profile Image Upload
             */
            $('.fileupload').fileupload({
                dropZone: $(this),
                url: profileImageUrl,
                dataType: 'json',
                singleFileUploads: true,
                formData: {'CSRF_TOKEN': csrfValue},
                limitMultiFileUploads: 1,
                progressall: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $('#space-image-upload-bar .progress-bar').css('width', progress + '%');
                },
                done: function (e, data) {

                    if (data.result.files.error == true) {
                        handleUploadError(data.result);
                    } else {
                        $('#space-profile-image').attr('src', data.result.files.url + '&c=' + Math.random());
                        $('#space-menu img').attr('src', data.result.files.url + '&c=' + Math.random());
                        $('#space-profile-image').addClass('animated bounceIn');
                    }

                    $('#space-image-upload-loader').hide();
                    $('#space-image-upload-bar .progress-bar').css('width', '0%');
                    $('#profile-image-upload-edit-button').show();


                }
            }).bind('fileuploadstart',function (e) {
                $('#space-image-upload-loader').show();
            }).bind('fileuploadstart', function (e) {
                $('#space-profile-image').removeClass('animated bounceIn');
            })

        });


    })


    // show buttons at image rollover
    $('#spaceimageupload').mouseover(function () {
        $('#space-image-upload-buttons').show();
    })

    // show buttons also at buttons rollover (better: prevent the mouseleave event)
    $('#space-image-upload-buttons').mouseover(function () {
        $('#space-image-upload-buttons').show();
    })

    // hide buttons at image mouse leave
    $('#spaceimageupload').mouseleave(function () {
        $('#space-image-upload-buttons').hide();
    })

    /**
     * Handle upload errors for profile and banner images
     */
    function handleUploadError(json) {

        $('#uploadErrorModal').appendTo(document.body);
        $('#uploadErrorModal .modal-dialog .modal-content .modal-body').html(json.files.errors.image);
        $('#uploadErrorModal').modal('show');

    }


    $(document).ready(function () {

        // override standard drag and drop behavior
        $(document).bind('drop dragover', function (e) {
            e.preventDefault();
        });

    });

</script>