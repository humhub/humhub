/**
 * Profile logic as Profile Image uploads
 *
 * @namespace humhub.modules.ui.profile
 **/
humhub.module('content.container.Header', function (module, require, $) {

    var Widget = require('ui.widget').Widget;
    var client = require('client');
    var Header = Widget.extend();

    Header.prototype.init = function () {
        this.$.find('.image-upload-container').on('mouseover', function () {
            $(this).find('.image-upload-buttons').show();
        }).on('mouseout', function () {
            $(this).find('.image-upload-buttons').hide();
        });

        this.$.find('.fileinput-button').each(function() {
            var $this = $(this);
            var upload =  Widget.instance($this.find('input[type="file"]'));
            var profileImage = new ProfileImage($this.closest('.image-upload-container'));
            upload.on('uploadStart', function() {
                profileImage.getLoader().show();
            }).on('uploadEnd', function(evt, response) {
                profileImage.getLoader().hide();
                var file = response.result.files[0];
                if(!file.error) {
                   profileImage.replaceImage(file);
                }
                upload.progress.reset();
            });
        });
    };

    Header.prototype.delete = function(evt) {
        var profileImage = new ProfileImage(evt.$trigger.closest('.image-upload-container'));

        client.post(evt).then(function(response) {
            if(response.defaultUrl) {
                profileImage.reset(response.defaultUrl, response.type);
            }
        }).catch(function(ex) {
            module.log.error(ex, true);
        });
    };

    ProfileImage = function($root) {
        this.$ = $root;
    };

    ProfileImage.prototype.replaceImage = function(file) {
        this.getAcronym().addClass('hidden'); //only for spaces

        var $image = this.getImage();

        // required for retriggering the animation
        $image.removeClass('animated bounceIn')[0].offsetWidth;

        var random = Math.random();

        $image.attr('src', file.url + '&c=' + random)
            .addClass('animated bounceIn').removeClass('hidden');

        this.getEditButtons().show();

        if(file.type === 'image') { // Only replace profile images
            var containerId = this.getContainerId();
            $('div[data-contentcontainer-id="'+containerId+'"].space-acronym').addClass('hidden');
            $('img[data-contentcontainer-id="'+containerId+'"]').attr('src', file.url + '&c=' + random).removeClass('hidden');
        }

    };

    ProfileImage.prototype.reset = function(defaultUrl, type) {
        var $image = this.getImage();
        var $acronym = this.getAcronym();
        var containerId = this.getContainerId();

        $image.addClass('hidden').attr('src', defaultUrl);


        if($acronym.length) { // Space only
            // required for retriggering the animation
            $acronym.removeClass('animated bounceIn')[0].offsetWidth;
            $acronym.addClass('animated bounceIn').removeClass('hidden');
            $('div[data-contentcontainer-id="'+containerId+'"].space-acronym').removeClass('hidden');
            $('img[data-contentcontainer-id="'+containerId+'"]').attr('src', defaultUrl).addClass('hidden');
        } else {
            $image.removeClass('hidden');
            $('img[data-contentcontainer-id="'+containerId+'"]').attr('src', defaultUrl).removeClass('hidden');
        }

        this.getEditButtons().hide();
    };

    ProfileImage.prototype.getContainerId = function() {
        return this.getImage().data('contentcontainer-id');
    };

    ProfileImage.prototype.getImage = function() {
        return this.$.find('img.img-profile-header-background');
    };

    ProfileImage.prototype.getLoader = function() {
        return this.$.find('.image-upload-loader');
    };

    ProfileImage.prototype.getAcronym = function() {
        return this.$.find('.space-acronym');
    };

    ProfileImage.prototype.getEditButtons = function() {
        return this.$.find('.profile-image-edit');
    };

    module.export = Header;
});
