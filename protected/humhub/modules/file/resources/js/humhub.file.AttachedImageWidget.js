/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

/**
 * Upload logic for record-attached images
 *
 * @namespace humhub.modules.file
 **/
humhub.module('file.AttachedImageWidget', function (module, require, $) {

    const version = 15
    console.debug('file.AttachedImageWidget v' + version)
    const Widget = require('ui.widget').Widget;
    const client = require('client');
    const AttachedImageWidget = Widget.extend();

    let $config = {};

    AttachedImageWidget.prototype.init = function (config) {
        module.log.debug('initializing file.AttachedImageWidget ...', {self: this, config, arguments})

        this.config = config;
        this.attachedImages = [];

        const $widget = this;

        $config = config;

        module.log.debug('Configuration ', {config, $config})

        this.$.find('.attached-image-upload-container').each(function () {
            const $this = $(this);
            const imageUploadButtons = $this.find('.attached-image-upload-buttons');

            // With no possibility to edit, the Upload Buttons won't be rendered. Hence, no need to do anything
            if (!imageUploadButtons) return;

            const $attachedImage = $this.attachedImage = new AttachedImage($this);
            const $image = $attachedImage.getImage();
            const $imageEditButtons = $attachedImage.getEditButtons();

            $widget.attachedImages.push($attachedImage);

            $this.on('mouseover', function () {

                if ($image.hasClass('attached-image-loaded'))
                    $imageEditButtons.show();
                else
                    $imageEditButtons.hide();

                imageUploadButtons.show();
            }).on('mouseout', function () {
                imageUploadButtons.hide();
            });

            $widget.$.find('.fileinput-button').each(function () {
                const $this = $(this);
                const upload = Widget.instance($this.find('input[type="file"]'));
                // const $attachedImage = new AttachedImage($this.closest('.attached-image-upload-container'));

                upload.on('uploadStart', function ($data) {
                    module.log.debug('uploadStart', $data);
                    $attachedImage.getLoader().show();
                }).on('uploadEnd', function (evt, response) {
                    module.log.debug("Response received:", response);
                    $attachedImage.getLoader().hide();
                    const file = response.result.files[0];
                    if (!file.error) {
                        $attachedImage.replaceImage(file);
                    }
                    upload.progress.reset();
                });
            });
        });
    };

    AttachedImageWidget.prototype.delete = function (evt) {
        // const attachedImage = new AttachedImage(evt.$trigger.closest('.attached-image-upload-container'));

        module.log.debug('delete', evt);

        client.post(evt).then(function (response) {
            if (response.defaultUrl) {
                $widget.attachedImage.reset(response.defaultUrl, response.type);
            }
        }).catch(function (ex) {
            module.log.error(ex, true);
        });
    };

    AttachedImage = function ($root) {
        this.$ = $root;
    };

    AttachedImage.prototype.replaceImage = function (file) {
        // this.getAcronym().addClass('hidden'); //only for spaces

        const $image = this.getImage();

        // required for re-triggering the animation
        $image.removeClass('animated bounceIn')[0].offsetWidth;

        const random = Math.random();

        $image.attr('src', file.url + '&c=' + random)
            .addClass('animated bounceIn').removeClass('hidden');

        this.getEditButtons().show();

        // if (file.type === 'image') { // Only replace profile images
        //     var containerId = this.getContainerId();
        //     $('div[data-contentcontainer-id="' + containerId + '"].space-acronym').addClass('hidden');
        //     $('img[data-contentcontainer-id="' + containerId + '"]').attr('src', file.url + '&c=' + random).removeClass('hidden');
        // }

    };

    AttachedImage.prototype.reset = function (defaultUrl, type) {
        const $image = this.getImage();
        const $acronym = this.getAcronym();
        const containerId = this.getContainerId();

        $image.addClass('hidden').attr('src', defaultUrl);


        if ($acronym.length) { // Space only
            // required for re-triggering the animation
            $acronym.removeClass('animated bounceIn')[0].offsetWidth;
            $acronym.addClass('animated bounceIn').removeClass('hidden');
            $('div[data-contentcontainer-id="' + containerId + '"].space-acronym').removeClass('hidden');
            $('img[data-contentcontainer-id="' + containerId + '"]').attr('src', defaultUrl).addClass('hidden');
        } else {
            $image.removeClass('hidden');
            $('img[data-contentcontainer-id="' + containerId + '"]').attr('src', defaultUrl).removeClass('hidden');
        }

        this.getEditButtons().hide();
    };

    AttachedImage.prototype.getContainerId = function () {
        return this.getImage().data('contentcontainer-id');
    };

    AttachedImage.prototype.getImage = function () {
        return this.image ?? (this.image = this.$.find('img.attached-image'));
    };

    AttachedImage.prototype.getLoader = function () {
        return this.loader ?? (this.loader = this.$.find('.attached-image-upload-loader'));
    };

    AttachedImage.prototype.getAcronym = function () {
        return this.$.find('.space-acronym');
    };

    AttachedImage.prototype.getEditButtons = function () {
        return this.editButtons ?? (this.editButtons = this.$.find('.attached-image-edit'));
    };

    module.export = AttachedImageWidget;
});
