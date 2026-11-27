humhub.module('ActiveFileUpload', function (module, require, $) {
    const Widget = require('ui.widget').Widget;
    const object = require('util').object;

    const Upload = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Upload, Widget);

    Upload.uploadWidget = null;
    Upload.previewWidget = null;

    Upload.prototype.init = function () {
        this.uploadWidget = Widget.instance(this.$.find('input[type="file"]'));
        this.previewWidget = Widget.instance(this.$.find('.img-uploader-preview'));

        const removeButton = this.$.find('.img-uploader-remove');

        if (this.previewWidget.hasFiles()) {
            removeButton.show();
        }

        this.uploadWidget.on('fileDeleted', () => {
            removeButton.hide();
        });

        this.uploadWidget.on('uploadFinish', () => {
            removeButton.show();
        });
    };

    Upload.prototype.upload = function () {
        this.uploadWidget.trigger('upload')
    };

    Upload.prototype.delete = function (event) {
        event.preventDefault()

        if (this.options.standalone) {
            // Standalone files are refused by the generic file/delete endpoint —
            // only clear the input, the owning form deletes the record on submit.
            const name = this.uploadWidget.options.uploadSubmitName || 'fileList[]';
            this.uploadWidget.$form.find('input[name="' + name + '"]').val('');
            this.$.find('.img-uploader-remove').hide();
            this.previewWidget.reset();
            return;
        }

        this.uploadWidget.delete({guid: this.previewWidget.$.find('.file-preview-item').first().attr('data-guid')});
        this.previewWidget.reset();
    };

    /**
     * The image of the field. `ActiveFileUpload` renders the container with the current file as the init data (as the
     * upload response has it, `FileHelper::getFileInfos()`).
     */
    const Preview = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Preview, Widget);

    Preview.prototype.init = function (files) {
        this.$list = $('<ul class="files"></ul>').appendTo(this.$);
        (Array.isArray(files) ? files : []).forEach((file) => {
            this.add(file);
        });

        if (this.hasFiles()) {
            this.$.show();
        } else {
            this.$.hide();
        }
    };

    // A single image: a new one replaces the one before
    Preview.prototype.add = function (file) {
        this.$list.html(
            $('<li></li>')
                .addClass('file-preview-item')
                .attr('data-guid', file.guid)
                .append($('<img>').attr({alt: file.name, src: file.thumbnailUrl}))
        );
    };

    Preview.prototype.getFileCount = function () {
        return this.$list.find('.file-preview-item').length;
    };

    Preview.prototype.hasFiles = function () {
        return this.getFileCount() > 0;
    };

    Preview.prototype.reset = function () {
        this.$list.empty();
        this.$.hide();
    };

    module.export({
        Upload: Upload,
        Preview: Preview,
    });
});
