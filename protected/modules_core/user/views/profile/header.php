<div class="panel panel-default panel-profile">

    <div class="panel-profile-header">

        <div class="image-upload-container" style="width: 100%; height: 100%;">
            <!-- profile image output-->
            <img class="img-profile-header-background" id="user-banner-image"
                 src="<?php echo $this->getUser()->getProfileBannerImage()->getUrl(); ?>"
                 width="100%" style="width: 100%;">

            <!-- check if the current user is the profile owner and can change the images -->
            <?php if (Yii::app()->user->id == $this->getUser()->id) { ?>
                <form class="fileupload" id="bannerfileupload" action="" method="POST" enctype="multipart/form-data"
                      style="position: absolute; top: 0; left: 0; opacity: 0; width: 100%; height: 100%;">
                    <input type="file" name="bannerfiles[]">
                </form>

                <?php
                // set standard padding for banner progressbar
                $padding = '140px 350px';

                // if the default banner image is displaying
                if (!$this->getUser()->getProfileBannerImage()->hasImage())
                {
                    // change padding to the lower image height
                    $padding = '70px 350px';
                }
                ?>

                <div class="image-upload-loader" id="banner-image-upload-loader" style="padding: <?php echo $padding ?>;">
                    <div class="progress image-upload-progess-bar" id="banner-image-upload-bar">
                        <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="00"
                             aria-valuemin="0"
                             aria-valuemax="100" style="width: 0%;">
                        </div>
                    </div>
                </div>

                <div class="img-profile-data">
                    <h1><?php echo $this->getUser()->displayName; ?></h1>

                    <h2><?php echo $this->getUser()->title; ?></h2>
                </div>

                <div class="image-upload-buttons" id="banner-image-upload-buttons">
                    <a href="javascript:$('#bannerfileupload input').click();" class="btn btn-info btn-sm"><i
                            class="fa fa-cloud-upload"></i></a>
                    <a id="banner-image-upload-edit-button"
                       style="<?php if (!$this->getUser()->getProfileBannerImage()->hasImage()) { echo 'display: none;'; } ?>"
                       href="<?php echo Yii::app()->createAbsoluteUrl('//user/profile/cropBannerImage'); ?>"
                       class="btn btn-info btn-sm" data-toggle="modal" data-target="#globalModal"><i
                            class="fa fa-edit"></i></a>
                </div>
            <?php } ?>

        </div>

        <div class="image-upload-container profile-user-photo-container" style="width: 140px; height: 140px;">

            <!-- profile image output-->
            <img class="img-rounded profile-user-photo" id="user-profile-image"
                 src="<?php echo $this->getUser()->getProfileImage()->getUrl(); ?>"
                 data-src="holder.js/140x140" alt="140x140" style="width: 140px; height: 140px;"/>

            <!-- check if the current user is the profile owner and can change the images -->
            <?php if (Yii::app()->user->id == $this->getUser()->id) { ?>
                <form class="fileupload" id="profilefileupload" action="" method="POST" enctype="multipart/form-data"
                      style="position: absolute; top: 0; left: 0; opacity: 0; height: 140px; width: 140px;">
                    <input type="file" name="profilefiles[]">
                </form>

                <div class="image-upload-loader" id="profile-image-upload-loader" style="padding-top: 60px;">
                    <div class="progress image-upload-progess-bar" id="profile-image-upload-bar">
                        <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="00"
                             aria-valuemin="0"
                             aria-valuemax="100" style="width: 0%;">
                        </div>
                    </div>
                </div>

                <div class="image-upload-buttons" id="profile-image-upload-buttons">
                    <a href="javascript:$('#profilefileupload input').click();" class="btn btn-info btn-sm"><i
                            class="fa fa-cloud-upload"></i></a>
                    <a id="profile-image-upload-edit-button"
                       style="<?php if (!$this->getUser()->getProfileImage()->hasImage()) { echo 'display: none;'; } ?>"
                       href="<?php echo Yii::app()->createAbsoluteUrl('//user/profile/cropProfileImage'); ?>"
                       class="btn btn-info btn-sm" data-toggle="modal" data-target="#globalModal"><i
                            class="fa fa-edit"></i></a>
                </div>
            <?php } ?>

        </div>


    </div>

    <div class="panel-body">

        <div class="panel-profile-controls">
            <!-- start: User statistics -->
            <div class="statistics pull-left">

                <div class="pull-left entry">
                    <span class="count"><?php echo count($this->getUser()->followerUser); ?></span></a>
                    <br>
                    <span class="title"><?php echo Yii::t('UserModule.profile', 'Followers'); ?></span>
                </div>

                <div class="pull-left entry">
                    <span class="count"><?php echo count($this->getUser()->followsUser); ?></span>
                    <br>
                    <span class="title"><?php echo Yii::t('UserModule.profile', 'Following'); ?></span>
                </div>

                <div class="pull-left entry">
                    <span class="count"><?php echo count($this->getUser()->spaces); ?></span><br>
                    <span class="title"><?php echo Yii::t('base', 'Spaces'); ?></span>
                </div>
            </div>
            <!-- end: User statistics -->


            <div class="controls pull-right">
                <!-- start: User following -->
                <?php
                if (Yii::app()->user->id != $this->getUser()->id) {
                    if ($this->getUser()->isFollowedBy(Yii::app()->user->id)) {
                        print CHtml::link("Unfollow", $this->createUrl('profile/unfollow', array('guid' => $this->getUser()->guid)), array('class' => 'btn btn-primary'));
                    } else {
                        print CHtml::link("Follow", $this->createUrl('profile/follow', array('guid' => $this->getUser()->guid)), array('class' => 'btn btn-success'));
                    }
                }
                ?>
                <!-- end: User following -->

                <!-- start: Edit profile -->
                <?php if (Yii::app()->user->id == $this->getUser()->id) { ?>
                    <!-- Edit user account (if this is your profile) -->
                    <a href="<?php echo $this->createUrl('//user/account/edit', array('guid' => $this->getUser()->guid)); ?>"
                       id="edit_profile" class="btn btn-primary">Edit account</a>
                <?php } ?>
                <!-- end: Edit profile -->
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
                    id="myModalLabel"><?php echo Yii::t('UserModule.account', '<strong>Something</strong> went wrong'); ?></h4>
            </div>
            <div class="modal-body text-center">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary"
                        data-dismiss="modal"><?php echo Yii::t('UserModule.account', 'Ok'); ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">


    /**
     * Handle Image Upload
     */
    $(function () {
        'use strict';
        // Change this to the location of your server-side upload handler:
        var profileImageUrl = '<?php echo Yii::app()->createUrl('//user/profile/profileImageUpload'); ?>';
        var bannerImageUrl = '<?php echo Yii::app()->createUrl('//user/profile/bannerImageUpload'); ?>';

        $('.fileupload').each(function () {


            if ($(this).attr('id') == "profilefileupload") {

                /**
                 * Handle Profile Image Upload
                 */
                $(this).fileupload({
                    dropZone: $(this),
                    url: profileImageUrl,
                    dataType: 'json',
                    singleFileUploads: true,
                    formData: {'CSRF_TOKEN': csrfValue},
                    limitMultiFileUploads: 1,
                    progressall: function (e, data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        $('#profile-image-upload-bar .progress-bar').css('width', progress + '%');
                    },
                    done: function (e, data) {

                        if (data.result.files.error == true) {
                            handleUploadError(data.result);
                        } else {
                            $('#user-account-image').attr('src', data.result.files.url + '&c=' + Math.random());
                            $('#user-profile-image').attr('src', data.result.files.url + '&c=' + Math.random());
                            $('.user-image').attr('src', data.result.files.url + '&c=' + Math.random());
                            $('#user-profile-image').addClass('animated bounceIn');
                        }

                        $('#profile-image-upload-loader').hide();
                        $('#profile-image-upload-bar .progress-bar').css('width', '0%');
                        $('#profile-image-upload-edit-button').show();


                    }
                }).bind('fileuploadstart',function (e) {
                    $('#profile-image-upload-loader').show();
                }).bind('fileuploadstart', function (e) {
                    $('#user-profile-image').removeClass('animated bounceIn');
                })

            } else if ($(this).attr('id') == "bannerfileupload") {

                /**
                 * Handle Banner Image Upload
                 */
                $(this).fileupload({
                    dropZone: $(this),
                    url: bannerImageUrl,
                    dataType: 'json',
                    singleFileUploads: true,
                    formData: {'CSRF_TOKEN': csrfValue},
                    limitMultiFileUploads: 1,
                    progressall: function (e, data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        $('#banner-image-upload-bar .progress-bar').css('width', progress + '%');
                    },
                    done: function (e, data) {

                        if (data.result.files.error == true) {
                            handleUploadError(data.result);
                        } else {
                            $('#user-banner-image').attr('src', data.result.files.url + '&c=' + Math.random());
                            $('#user-banner-image').addClass('animated bounceIn');
                        }

                        $('#banner-image-upload-loader').hide();
                        $('#banner-image-upload-bar .progress-bar').css('width', '0%');
                        $('#banner-image-upload-edit-button').show();


                    }
                }).bind('fileuploadstart',function (e) {
                    $('#banner-image-upload-loader').show();
                }).bind('fileuploadstart', function (e) {
                    $('#user-banner-image').removeClass('animated bounceIn');
                })

            }


        });


    })


    // show buttons at image rollover
    $('#profilefileupload').mouseover(function () {
        $('#profile-image-upload-buttons').show();
    })

    // show buttons also at buttons rollover (better: prevent the mouseleave event)
    $('#profile-image-upload-buttons').mouseover(function () {
        $('#profile-image-upload-buttons').show();
    })

    // hide buttons at image mouse leave
    $('#profilefileupload').mouseleave(function () {
        $('#profile-image-upload-buttons').hide();
    })


    // show buttons at image rollover
    $('#bannerfileupload, .img-profile-data').mouseover(function () {
        $('#banner-image-upload-buttons').show();
    })

    // show buttons also at buttons rollover (better: prevent the mouseleave event)
    $('#banner-image-upload-buttons').mouseover(function () {
        $('#banner-image-upload-buttons').show();
    })

    // hide buttons at image mouse leave
    $('#bannerfileupload, .img-profile-data').mouseleave(function () {
        $('#banner-image-upload-buttons').hide();
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